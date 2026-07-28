#!/bin/bash
# =============================================================================
# provision-tenant.sh — cria um novo tenant no VPS
#
#   Uso:  provision-tenant.sh <site> <dominio> [--with-demo]
#         provision-tenant.sh --destroy <site> --yes
#
#   Ex.:  provision-tenant.sh friedrichveiculos friedrichveiculos.com.br
#
#   --with-demo  também cria os 9 veículos de demonstração do VehicleSeeder,
#                com fotos de public/img/veiculos/. Sem a flag, o tenant nasce
#                com estoque vazio — que é o correto para cliente real.
#
# Substitui os passos manuais de docs/novo-tenant.md. Idempotente onde é seguro
# ser, e aborta em qualquer falha (set -euo pipefail).
#
# Rodar como root. Requer: nginx, php8.3-fpm, mysql, certbot, git, composer.
# =============================================================================
set -euo pipefail

REPO="${REPO:-https://github.com/jfbritto/soavel.git}"
PHP=php8.3
COMPOSER=/usr/local/bin/composer
VPS_IP="${VPS_IP:-129.121.50.200}"

# Sites que este script NUNCA pode destruir
PROTEGIDOS="soavelveiculos friedrichveiculos masterveiculos rocanossa taketicket treinaedu helpflux"

log()  { printf '\n\033[1;34m==> %s\033[0m\n' "$*"; }
ok()   { printf '    \033[32m✓\033[0m %s\n' "$*"; }
erro() { printf '\n\033[1;31mERRO: %s\033[0m\n' "$*" >&2; exit 1; }

[ "$(id -u)" -eq 0 ] || erro "rode como root"

# ─────────────────────────────────────────────────────────────────────────────
# --destroy
# ─────────────────────────────────────────────────────────────────────────────
if [ "${1:-}" = "--destroy" ]; then
  SITE="${2:?falta o nome do site}"
  [ "${3:-}" = "--yes" ] || erro "destruir exige --yes explícito: provision-tenant.sh --destroy $SITE --yes"

  for P in $PROTEGIDOS; do
    [ "$SITE" = "$P" ] && erro "'$SITE' está na lista de protegidos. Remova da variável PROTEGIDOS se realmente quiser."
  done

  log "Destruindo tenant $SITE"
  rm -f  "/etc/nginx/sites-enabled/$SITE"
  rm -f  "/etc/nginx/sites-available/$SITE"
  rm -f  "/etc/php/8.3/fpm/pool.d/$SITE.conf"
  nginx -t && systemctl reload nginx && ok "nginx"
  php-fpm8.3 -t && systemctl reload php8.3-fpm && ok "php-fpm"
  mysql -e "DROP DATABASE IF EXISTS \`$SITE\`; DROP USER IF EXISTS '$SITE'@'localhost'; FLUSH PRIVILEGES;" && ok "banco e usuário"
  rm -rf "/var/www/$SITE" && ok "diretório"
  rm -f  "/home/deploy/deploy-$SITE.sh" "/root/migracao/$SITE-dbpass.txt"
  git config --global --unset-all "safe.directory" "/var/www/$SITE" 2>/dev/null || true
  log "Tenant $SITE removido. O certificado em /etc/letsencrypt NÃO foi apagado (use: certbot delete --cert-name <dominio>)."
  exit 0
fi

# ─────────────────────────────────────────────────────────────────────────────
# provisionamento
# ─────────────────────────────────────────────────────────────────────────────
SITE="${1:?Uso: provision-tenant.sh <site> <dominio> [--with-demo]}"
DOMAIN="${2:?Uso: provision-tenant.sh <site> <dominio> [--with-demo]}"
APP="/var/www/$SITE"
COM_DEMO=0
[ "${3:-}" = "--with-demo" ] && COM_DEMO=1

echo "site=$SITE  dominio=$DOMAIN  repo=$REPO  demo=$COM_DEMO"

# ── 0. pré-condições ────────────────────────────────────────────────────────
log "Verificando pré-condições"
[ -d "$APP" ] && erro "$APP já existe"
mysql -N -e "SHOW DATABASES LIKE '$SITE';" | grep -q . && erro "banco '$SITE' já existe"
[ -f "/etc/nginx/sites-available/$SITE" ] && erro "vhost de $SITE já existe"
command -v $PHP >/dev/null || erro "$PHP não encontrado"
[ -x "$COMPOSER" ] || erro "composer não encontrado em $COMPOSER"
ok "nada conflitante"

# O DNS precisa apontar para cá ANTES do certbot (validação HTTP-01)
DNS_IP=$(dig +short A "$DOMAIN" @8.8.8.8 2>/dev/null | head -1)
if [ "$DNS_IP" != "$VPS_IP" ]; then
  echo
  echo "    AVISO: $DOMAIN resolve para '${DNS_IP:-nada}', não para $VPS_IP."
  echo "    O site será provisionado em HTTP, mas o certificado SSL falharia."
  echo "    Aponte o DNS e rode depois:  certbot --nginx -d $DOMAIN -d www.$DOMAIN"
  SKIP_SSL=1
else
  SKIP_SSL=0
  ok "DNS aponta para este VPS"
fi

umask 022

# ── 1. código ───────────────────────────────────────────────────────────────
log "Clonando o repositório"
git clone --quiet "$REPO" "$APP"
git config --global --get-all safe.directory | grep -qx "$APP" \
  || git config --global --add safe.directory "$APP"
ok "$(git -C "$APP" log --oneline -1)"

log "Instalando dependências (PHP 8.3 explícito — o php default do servidor é 8.4)"
( cd "$APP" && $PHP "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --quiet )
ok "vendor instalado"

# ── 2. banco e usuário isolado ──────────────────────────────────────────────
log "Criando banco e usuário"
# O sufixo _1aZ garante as 4 classes exigidas pelo validate_password (MEDIUM):
# minúscula, maiúscula, dígito e caractere especial. Sem isso: ERROR 1819.
# Especiais limitados a _ - . para não quebrar o sed do .env.
DB_PASS="$(openssl rand -base64 32 | tr -dc 'A-Za-z0-9' | head -c 26)_1aZ"
mysql <<SQL
CREATE DATABASE \`$SITE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER '$SITE'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$SITE\`.* TO '$SITE'@'localhost';
FLUSH PRIVILEGES;
SQL
mkdir -p /root/migracao
( umask 077; echo "$DB_PASS" > "/root/migracao/$SITE-dbpass.txt" )
ok "banco utf8mb4_unicode_ci, usuário restrito, senha em /root/migracao/$SITE-dbpass.txt"

# Confirma o isolamento em vez de presumir.
# O `|| true` interno e obrigatorio: quando o isolamento esta CORRETO o grep nao
# encontra nada e sai com status 1, que o pipefail propagaria e o set -e mataria
# o script — falhando justamente quando a verificacao passa.
VISIVEIS=$( { mysql -N -u "$SITE" -p"$DB_PASS" -e "SHOW DATABASES;" 2>/dev/null \
  | grep -vE '^(information_schema|performance_schema)$' \
  | grep -vx "$SITE" || true; } | wc -l )
[ "$VISIVEIS" -eq 0 ] || erro "o usuário $SITE vê $VISIVEIS banco(s) de outros tenants — GRANT vazou"
ok "isolamento confirmado"

# ── 3. .env ─────────────────────────────────────────────────────────────────
log "Gerando .env"
ADMIN_EMAIL="admin@$DOMAIN"
ADMIN_PASS="$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 18)"
cat > "$APP/.env" <<ENV
APP_NAME="$SITE"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://$DOMAIN

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$SITE
DB_USERNAME=$SITE
DB_PASSWORD=$DB_PASS

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=43200

MAIL_MAILER=log
MAIL_FROM_ADDRESS=nao-responda@$DOMAIN
MAIL_FROM_NAME="\${APP_NAME}"

MASTER_API_TOKEN=PREENCHER_COM_O_TOKEN_DO_MASTER

# Lidos pelo AdminUserSeeder. Sem definir aqui, ele cairia no default
# admin@admin.com / admin123 — credencial publica num site ao vivo.
ADMIN_EMAIL=$ADMIN_EMAIL
ADMIN_PASSWORD=$ADMIN_PASS
ENV
( cd "$APP" && $PHP artisan key:generate --force --quiet )
grep -q '^APP_KEY=base64:' "$APP/.env" || erro "APP_KEY não foi gerada"
ok "APP_KEY gerada, MAIL_MAILER=log, APP_DEBUG=false"

# ── 4. schema ───────────────────────────────────────────────────────────────
log "Criando o schema"
( cd "$APP" && $PHP artisan migrate --force )
ok "migrations aplicadas"

# Seeders escolhidos um a um, de propósito. O DatabaseSeeder chamaria também o
# VehicleSeeder, que cria 9 veiculos de demonstracao com fotos de
# public/img/veiculos/ — lixo que o cliente real teria de apagar.
log "Populando dados iniciais"
( cd "$APP" && $PHP artisan db:seed --class=AdminUserSeeder --force --quiet )
( cd "$APP" && $PHP artisan db:seed --class=SettingSeeder   --force --quiet )
ok "administrador e configuracoes padrao"

if [ "$COM_DEMO" -eq 1 ]; then
  ( cd "$APP" && $PHP artisan db:seed --class=VehicleSeeder --force )
  ok "9 veiculos de demonstracao (--with-demo)"
else
  ok "estoque vazio (use --with-demo para dados de demonstracao)"
fi

# Confirma que nao ficou credencial default. Usa `if` em vez de `cmd && erro`
# porque com set -e o comportamento de listas && é fácil de errar.
if mysql -N "$SITE" -e "SELECT email FROM users;" 2>/dev/null | grep -qx 'admin@admin.com'; then
  erro "usuario admin@admin.com foi criado — o ADMIN_EMAIL do .env nao foi lido pelo seeder"
fi
ok "nenhuma credencial default no banco"

# ── 5. storage e permissões ─────────────────────────────────────────────────
log "Symlink e permissões"
( cd "$APP" && rm -f public/storage && $PHP artisan storage:link --quiet )
chown -R deploy:www-data "$APP"
find "$APP" -type d -not -path '*/.git/*' -exec chmod 755 {} \;
find "$APP" -type f -not -path '*/.git/*' -exec chmod 644 {} \;
# setgid: uploads futuros do PHP herdam o grupo www-data
find "$APP/storage" "$APP/bootstrap/cache" -type d -exec chmod 2775 {} \;
find "$APP/storage" "$APP/bootstrap/cache" -type f -exec chmod 664 {} \;
chmod 640 "$APP/.env"
ok "deploy:www-data, 2775 com setgid, .env 640"

# ── 6. caches (DEPOIS deles vêm as permissões — a ordem importa) ────────────
log "Gerando caches de produção"
( cd "$APP" && $PHP artisan config:cache --quiet && $PHP artisan route:cache --quiet && $PHP artisan view:cache --quiet )
find "$APP/storage" "$APP/bootstrap/cache" -type f -exec chmod 664 {} \;
ILEGIVEIS=$(find "$APP/storage" "$APP/bootstrap/cache" -type f ! -perm -g=r | wc -l)
[ "$ILEGIVEIS" -eq 0 ] || erro "$ILEGIVEIS arquivos ilegíveis pelo grupo www-data — o site retornaria HTTP 500"
sudo -u www-data test -r "$APP/bootstrap/cache/config.php" || erro "www-data não lê o config cache"
ok "caches gerados e legíveis pelo www-data"

# ── 7. pool PHP-FPM dedicado ────────────────────────────────────────────────
# Dedicado porque o php.ini global tem upload_max_filesize=2M, insuficiente
# para foto de veículo. Alterar o php.ini afetaria todos os sites do servidor.
log "Criando pool PHP-FPM dedicado"
cat > "/etc/php/8.3/fpm/pool.d/$SITE.conf" <<POOL
[$SITE]
user = www-data
group = www-data

listen = /run/php/php8.3-fpm-$SITE.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = ondemand
pm.max_children = 4
pm.process_idle_timeout = 30s
pm.max_requests = 500

php_admin_value[upload_max_filesize] = 20M
php_admin_value[post_max_size] = 24M
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 120
php_admin_value[max_file_uploads] = 30
php_admin_value[error_log] = /var/log/php8.3-fpm-$SITE.log
php_admin_flag[log_errors] = on
POOL
php-fpm8.3 -t || erro "sintaxe do pool inválida — NÃO recarreguei (protegeria os outros sites)"
systemctl reload php8.3-fpm
sleep 2
[ -S "/run/php/php8.3-fpm-$SITE.sock" ] || erro "socket não foi criado"
ok "pool ondemand, upload 20M, socket ativo"

# ── 8. vhost nginx ──────────────────────────────────────────────────────────
log "Criando vhost nginx"
cat > "/etc/nginx/sites-available/$SITE" <<VHOST
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;
    root $APP/public;
    index index.php;

    charset utf-8;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    # >= post_max_size do pool, senão o nginx corta antes com 413
    client_max_body_size 24M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php8.3-fpm-$SITE.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }

    access_log /var/log/nginx/$SITE-access.log;
    error_log  /var/log/nginx/$SITE-error.log;
}
VHOST
ln -sfn "/etc/nginx/sites-available/$SITE" "/etc/nginx/sites-enabled/$SITE"
nginx -t || erro "sintaxe do nginx inválida — NÃO recarreguei (protegeria os outros sites)"
systemctl reload nginx
sleep 2
ok "vhost ativo"

# ── 9. SSL ──────────────────────────────────────────────────────────────────
if [ "$SKIP_SSL" -eq 0 ]; then
  log "Emitindo certificado (HTTP-01 via nginx, com renovação automática)"
  certbot --nginx --cert-name "$DOMAIN" -d "$DOMAIN" -d "www.$DOMAIN" \
    --agree-tos --no-eff-email --non-interactive --redirect
  grep -q '^authenticator = nginx' "/etc/letsencrypt/renewal/$DOMAIN.conf" \
    || erro "authenticator não é nginx — a renovação automática falharia"
  ok "certificado emitido, authenticator = nginx"
else
  log "SSL ignorado (DNS ainda não aponta para cá)"
fi

# ── 10. script de deploy ────────────────────────────────────────────────────
log "Criando o wrapper de deploy"
cat > "/home/deploy/deploy-$SITE.sh" <<WRAP
#!/bin/bash
exec /home/deploy/deploy-tenant.sh $SITE $DOMAIN
WRAP
chmod 755 "/home/deploy/deploy-$SITE.sh"
ok "/home/deploy/deploy-$SITE.sh"

# ── 11. verificação ─────────────────────────────────────────────────────────
log "Verificando"
PROTO=$([ "$SKIP_SSL" -eq 0 ] && echo https || echo http)
PORT=$([ "$SKIP_SSL" -eq 0 ] && echo 443 || echo 80)
for P in / /robots.txt; do
  CODE=$(curl -so /dev/null -w '%{http_code}' --max-time 25 \
    --resolve "$DOMAIN:$PORT:127.0.0.1" "$PROTO://$DOMAIN$P")
  printf '    %-14s HTTP %s\n' "$P" "$CODE"
  [ "$CODE" = "200" ] || erro "$P retornou $CODE"
done

LIMITE=$(cd "$APP" && $PHP -r 'echo ini_get("upload_max_filesize");')
echo "    upload_max_filesize no CLI: $LIMITE (o que vale é o do pool: 20M)"

log "Tenant $SITE provisionado"
cat <<FIM

    Diretório : $APP
    Banco     : $SITE (usuário isolado, senha em /root/migracao/$SITE-dbpass.txt)
    Pool FPM  : /run/php/php8.3-fpm-$SITE.sock  (upload 20M)
    Vhost     : /etc/nginx/sites-available/$SITE
    Deploy    : /home/deploy/deploy-$SITE.sh
    Backup    : automático — /home/deploy/backup.sh varre /var/www/*/

    ACESSO AO ADMIN — anote agora, a senha não é recuperável:

      URL   : https://$DOMAIN/admin
      Email : $ADMIN_EMAIL
      Senha : $ADMIN_PASS

    (também está em $APP/.env como ADMIN_PASSWORD)

    PASSOS MANUAIS QUE FALTAM:

    1. Cadastrar o tenant no Master em veiculos.helpflux.com.br/tenants
       e copiar o API Token gerado para o .env:
         sed -i 's|^MASTER_API_TOKEN=.*|MASTER_API_TOKEN=<token>|' $APP/.env
         cd $APP && $PHP artisan config:cache

    2. Trocar a senha do administrador no primeiro acesso.

    3. Configurar identidade visual e dados da loja em /admin/settings

FIM
