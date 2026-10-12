#!/bin/bash
set -e

# Fix permissions for www-data (UID 1000)
chown -R www-data:www-data /var/www/html/application
chmod -R 775 /var/www/html/application
find /var/www/html/application -type f -exec chmod 664 {} \;

# Ensure uploads directory exists and is writable
mkdir -p /var/www/html/assets/uploads
mkdir -p /var/www/html/assets/userImage
chown -R www-data:www-data /var/www/html/assets/uploads
chown -R www-data:www-data /var/www/html/assets/userImage
chmod -R 775 /var/www/html/assets/uploads
chmod -R 775 /var/www/html/assets/userImage

# Generate .env from .env.example if not exists
ENV_FILE="/var/www/html/application/.env"
EXAMPLE_FILE="/var/www/html/application/.env.example"

# Variáveis que DEVEM ser sincronizadas do container env para .env (sempre)
SYNC_VARS=(
    "DB_HOSTNAME"
    "DB_USERNAME"
    "DB_PASSWORD"
    "DB_DATABASE"
    "APP_BASEURL"
    "API_ENABLED"
    "API_TOKEN_EXPIRE_TIME"
)

# Variáveis que NÃO devem ser sobrescritas após primeira geração
IMMUTABLE_VARS=(
    "APP_ENCRYPTION_KEY"
    "API_JWT_KEY"
)

# Função: sincroniza uma variável no .env
sync_env_var() {
    local var_name="$1"
    local value="${!var_name:-}"
    
    if [ -z "$value" ]; then
        return 0
    fi
    
    # Verifica se é variável imutável e já existe no .env
    for immutable in "${IMMUTABLE_VARS[@]}"; do
        if [ "$var_name" = "$immutable" ] && grep -q "^${var_name}=" "$ENV_FILE" 2>/dev/null; then
            echo "  [SKIP] $var_name (imutável, já definida)"
            return 0
        fi
    done
    
    # Substitui ou adiciona a variável
    if grep -q "^${var_name}=" "$ENV_FILE" 2>/dev/null; then
        sed -i "s|^${var_name}=.*|${var_name}=${value}|" "$ENV_FILE"
        echo "  [UPDATE] $var_name"
    else
        echo "${var_name}=${value}" >> "$ENV_FILE"
        echo "  [ADD] $var_name"
    fi
}

# Primeira execução: cria .env do .env.example
if [ ! -f "$ENV_FILE" ] && [ -f "$EXAMPLE_FILE" ]; then
    echo "Gerando .env a partir de .env.example..."
    cp "$EXAMPLE_FILE" "$ENV_FILE"
    
    # Substituir placeholders pelas variáveis de ambiente do container
    sed -i "s|enter_db_hostname|${DB_HOSTNAME:-mysql}|g" "$ENV_FILE"
    sed -i "s|enter_db_username|${DB_USERNAME:-mapos}|g" "$ENV_FILE"
    sed -i "s|enter_db_password|${DB_PASSWORD:-mapos}|g" "$ENV_FILE"
    sed -i "s|enter_db_name|${DB_DATABASE:-mapos}|g" "$ENV_FILE"
    sed -i "s|enter_baseurl|${APP_BASEURL:-http://localhost:8000/}|g" "$ENV_FILE"
    
    # Gerar chaves aleatórias (apenas na primeira vez)
    ENCRYPTION_KEY=$(openssl rand -base64 32)
    sed -i "s|enter_encryption_key|${ENCRYPTION_KEY}|g" "$ENV_FILE"
    
    JWT_KEY=$(openssl rand -base64 32)
    sed -i "s|enter_jwt_key|${JWT_KEY}|g" "$ENV_FILE"
    
    sed -i "s|enter_token_expire_time|${API_TOKEN_EXPIRE_TIME:-3600}|g" "$ENV_FILE"
    sed -i "s|enter_api_enabled|${API_ENABLED:-false}|g" "$ENV_FILE"
    
    # Mudar ambiente para production
    sed -i "s|pre_installation|production|g" "$ENV_FILE"
    
    # Fix permissions on generated .env
    chown www-data:www-data "$ENV_FILE"
    chmod 664 "$ENV_FILE"
    
    echo ".env gerado com sucesso."
fi

# SEMPRE sincroniza variáveis configuráveis (mesmo se .env já existia)
echo "Sincronizando variáveis de ambiente..."
for var in "${SYNC_VARS[@]}"; do
    sync_env_var "$var"
done

# Garante que chaves imutáveis existam (caso .env tenha sido criado manualmente sem elas)
for var in "${IMMUTABLE_VARS[@]}"; do
    if ! grep -q "^${var}=" "$ENV_FILE" 2>/dev/null; then
        if [ "$var" = "APP_ENCRYPTION_KEY" ]; then
            ENCRYPTION_KEY=$(openssl rand -base64 32)
            echo "${var}=${ENCRYPTION_KEY}" >> "$ENV_FILE"
            echo "  [GEN] $var"
        elif [ "$var" = "API_JWT_KEY" ]; then
            JWT_KEY=$(openssl rand -base64 32)
            echo "${var}=${JWT_KEY}" >> "$ENV_FILE"
            echo "  [GEN] $var"
        fi
    fi
done

# Fix permissions on .env
chown www-data:www-data "$ENV_FILE"
chmod 664 "$ENV_FILE"

# Wait for MySQL to be ready and run banco.sql if database is empty
echo "Aguardando MySQL..."
while ! mysqladmin ping -h"${DB_HOSTNAME:-mysql}" -u"${DB_USERNAME:-mapos}" -p"${DB_PASSWORD:-mapos}" --silent; do
    sleep 2
done
echo "MySQL pronto."

# Check if tables exist (check for 'usuarios' table which is created by banco.sql)
DB_EXISTS=$(mysql -h"${DB_HOSTNAME:-mysql}" -u"${DB_USERNAME:-mapos}" -p"${DB_PASSWORD:-mapos}" "${DB_DATABASE:-mapos}" -e "SHOW TABLES LIKE 'usuarios';" 2>/dev/null | grep -c usuarios || true)

if [ "$DB_EXISTS" -eq 0 ]; then
    echo "Banco vazio detectado. Executando banco.sql..."
    mysql -h"${DB_HOSTNAME:-mysql}" -u"${DB_USERNAME:-mapos}" -p"${DB_PASSWORD:-mapos}" "${DB_DATABASE:-mapos}" < /var/www/html/banco.sql
    echo "Schema inicial criado."

    # Verifica se deve criar admin automático (variáveis definidas no docker/.env)
    if [ -n "${ADMIN_EMAIL:-}" ] && [ -n "${ADMIN_PASSWORD:-}" ] && [ -n "${ADMIN_NAME:-}" ]; then
        echo "Configurando admin automático..."

        # Gera hash da senha usando PHP
        ADMIN_PASSWORD_HASH=$(php -r "echo password_hash('$ADMIN_PASSWORD', PASSWORD_DEFAULT);")
        ADMIN_CREATED_AT=$(date '+%Y-%m-%d %H:%M:%S')

        # Atualiza o admin dummy inserido pelo banco.sql
        mysql -h"${DB_HOSTNAME:-mysql}" -u"${DB_USERNAME:-mapos}" -p"${DB_PASSWORD:-mapos}" "${DB_DATABASE:-mapos}" \
            -e "UPDATE usuarios SET nome = '${ADMIN_NAME}', email = '${ADMIN_EMAIL}', senha = '${ADMIN_PASSWORD_HASH}', dataCadastro = '${ADMIN_CREATED_AT}' WHERE email = 'admin_email' OR email = 'admin@mapos.local';" 2>/dev/null || true

        echo "Admin automático criado: ${ADMIN_EMAIL}"
    else
        # Remove admin dummy inserido pelo banco.sql (placeholders literais)
        # Para permitir que firstAccess funcione
        mysql -h"${DB_HOSTNAME:-mysql}" -u"${DB_USERNAME:-mapos}" -p"${DB_PASSWORD:-mapos}" "${DB_DATABASE:-mapos}" \
            -e "DELETE FROM usuarios WHERE email = 'admin_email' OR email = 'admin@mapos.local';" 2>/dev/null || true
        echo "Admin dummy removido. firstAccess estará disponível."
    fi
else
    echo "Banco já possui tabelas. Pulando banco.sql."
fi

# Start supervisord (php-fpm + cron)
exec /usr/bin/supervisord