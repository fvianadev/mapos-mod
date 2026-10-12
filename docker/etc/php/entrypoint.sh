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

if [ ! -f "$ENV_FILE" ] && [ -f "$EXAMPLE_FILE" ]; then
    echo "Gerando .env a partir de .env.example..."
    cp "$EXAMPLE_FILE" "$ENV_FILE"

    # Substituir placeholders pelas variáveis de ambiente do container
    sed -i "s|enter_db_hostname|${DB_HOSTNAME:-mysql}|g" "$ENV_FILE"
    sed -i "s|enter_db_username|${DB_USERNAME:-mapos}|g" "$ENV_FILE"
    sed -i "s|enter_db_password|${DB_PASSWORD:-mapos}|g" "$ENV_FILE"
    sed -i "s|enter_db_name|${DB_DATABASE:-mapos}|g" "$ENV_FILE"
    sed -i "s|enter_baseurl|${APP_BASEURL:-http://localhost:8000/}|g" "$ENV_FILE"

    # Gerar chaves aleatórias
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
else
    echo "Banco já possui tabelas. Pulando banco.sql."
fi

# Start supervisord (php-fpm + cron)
exec /usr/bin/supervisord