# Instalação do Map-OS com Docker

## Pré-requisitos

- Docker Engine 20.10+
- Docker Compose v2 (plugin `docker compose`)
- Git

---

## 1. Clone do repositório

```bash
git clone https://github.com/fvianadev/mapos-mod.git
cd mapos-mod
```

---

## 2. Configuração de ambiente

```bash
cd docker
cp .env.example .env   # se existir, senão crie do zero
```

Edite o arquivo `.env` com seus dados:

```env
# Banco de dados
DB_HOSTNAME=mysql
DB_USERNAME=mapos
DB_PASSWORD=senha_forte_aqui
DB_DATABASE=mapos

# URL pública de acesso (OBRIGATÓRIO - use a URL exata que acessará no navegador)
APP_BASEURL="http://192.168.1.202:8081/"

# Opcional - Admin automático (se não definir, usa firstAccess web)
# ADMIN_NAME="Administrador"
# ADMIN_EMAIL="admin@dominio.com"
# ADMIN_PASSWORD="senha_forte_123"

# Opcional - API
API_ENABLED=false
API_TOKEN_EXPIRE_TIME=3600
```

> **Importante:** `APP_BASEURL` deve terminar com `/` e ser a URL exata de acesso (IP, domínio, porta).

---

## 3. Subir os containers

```bash
# Build e start (primeira vez)
docker compose up --build -d

# Acompanhar logs do entrypoint (setup automático)
docker logs -f php-fpm
```

### O que acontece no primeiro start:

1. **Entrypoint corrige permissões** nas pastas `application/`, `assets/uploads/`, `assets/userImage/`
2. **Gera `application/.env`** a partir do `.env.example` + variáveis do `docker/.env`
3. **Aguarda MySQL** ficar disponível
4. **Executa `banco.sql`** se banco estiver vazio (cria schema completo)
5. **Remove admin dummy** do `banco.sql` para habilitar `firstAccess`
6. **Inicia PHP-FPM + Cron** via Supervisord

---

## 4. Primeiro acesso

Acesse no navegador: `http://SEU_IP:PORTA/` (ex: `http://192.168.1.202:8081/`)

Será redirecionado automaticamente para: `/login/firstAccess`

Preencha o formulário:
- **Nome:** Administrador do sistema
- **Email:** seu email real (recebe notificações)
- **Senha:** mínima 6 caracteres
- **Confirmar Senha**

Clique em **Criar usuário e entrar**.

---

## 5. Comandos úteis

```bash
# Ver logs em tempo real
docker logs -f php-fpm

# Ver status dos containers
docker compose ps

# Reiniciar apenas PHP-FPM (após mudar .env)
docker compose restart php-fpm

# Parar tudo
docker compose down

# Parar e remover volumes (banco de dados) - LIMPA TUDO
docker compose down -v

# Acessar container PHP como www-data
docker exec -u www-data -it php-fpm bash

# Rodar migrations manuais
docker exec -u www-data php-fpm php index.php tools migrate

# Backup do banco
docker exec mysql mysqldump -u mapos -p mapos > backup_$(date +%F).sql
```

---

## 6. Atualização

```bash
cd /caminho/do/projeto
git pull origin dev
docker compose down
docker compose up --build -d
```

> O entrypoint **não reexecuta** `banco.sql` se tabelas já existirem. Para migrations pendentes, acesse o painel > Configurações > Atualizar Banco.

---

## 7. Estrutura de volumes

| Container | Volume | Conteúdo |
|-----------|--------|----------|
| mysql | `./data/db/mysql:/var/lib/mysql` | Dados do MySQL |
| php-fpm | `../:/var/www/html` | Código fonte + uploads |
| nginx | `./etc/nginx/default.conf:/etc/nginx/conf.d/default.conf` | Config nginx |

---

## 8. Solução de problemas

### Erro de permissão em uploads
```bash
# O entrypoint já corrige, mas se precisar forçar:
docker exec -u root php-fpm chown -R www-data:www-data /var/www/html/assets/uploads /var/www/html/assets/userImage
docker exec -u root php-fpm chmod -R 775 /var/www/html/assets/uploads /var/www/html/assets/userImage
```

### MySQL não inicia
```bash
docker logs mysql
# Verifique se a porta 3306 não está em uso no host
```

### APP_BASEURL incorreto
```bash
# 1. Corrija no docker/.env
# 2. Reinicie php-fpm
docker compose restart php-fpm
# 3. Verifique se application/.env foi atualizado
cat ../application/.env | grep APP_BASEURL
```

### firstAccess não aparece
```bash
# Verifique se tabela usuarios está vazia
docker exec mysql mysql -u mapos -p mapos -e "SELECT * FROM usuarios;"
# Se houver registro, delete manualmente:
docker exec mysql mysql -u mapos -p mapos -e "DELETE FROM usuarios;"
# Reinice php-fpm
docker compose restart php-fpm
```

---

## 9. Variáveis de ambiente completas

| Variável | Obrigatória | Default | Descrição |
|----------|-------------|---------|-----------|
| `DB_HOSTNAME` | Sim | `mysql` | Host do MySQL (nome do serviço) |
| `DB_USERNAME` | Sim | `mapos` | Usuário do banco |
| `DB_PASSWORD` | Sim | `mapos` | Senha do banco |
| `DB_DATABASE` | Sim | `mapos` | Nome do database |
| `APP_BASEURL` | **Sim** | `http://localhost:8000/` | URL pública completa com `/` final |
| `ADMIN_NAME` | Não | - | Nome do admin (se quiser pular firstAccess) |
| `ADMIN_EMAIL` | Não | - | Email do admin |
| `ADMIN_PASSWORD` | Não | - | Senha do admin |
| `API_ENABLED` | Não | `false` | Habilita API REST |
| `API_TOKEN_EXPIRE_TIME` | Não | `3600` | Expiração token JWT (segundos) |

---

## 10. Produção - Checklist

- [ ] `APP_BASEURL` com HTTPS
- [ ] `DB_PASSWORD` forte (32+ chars)
- [ ] `APP_ENCRYPTION_KEY` gerada no `.env` (32 bytes base64)
- [ ] `API_JWT_KEY` forte se API habilitada
- [ ] `APP_CSRF_PROTECTION=true`
- [ ] `APP_COOKIE_SECURE=true` (se HTTPS)
- [ ] `APP_SESS_MATCH_IP=true`
- [ ] Backup automático do volume `./data/db/mysql`
- [ ] Monitoramento de logs (Loki, ELK, etc.)
- [ ] Healthcheck no docker-compose para mysql/php-fpm

---

## Suporte

- Issues: https://github.com/fvianadev/mapos-mod/issues
- Email: contato@mapos.com.br