#!/bin/bash
# =============================================================================
# deploy-tenant.sh — deploy de um tenant no VPS
#
#   Uso:  deploy-tenant.sh <site> <dominio>
#   Ex.:  deploy-tenant.sh soavelveiculos soavelveiculos.com.br
#
# Os wrappers em /home/deploy/deploy-<site>.sh chamam este script.
# Rodar como root (faz chown e systemctl reload).
# =============================================================================
set -euo pipefail

SITE="${1:?falta o nome do site}"
DOMAIN="${2:?falta o dominio}"
APP="/var/www/$SITE"
PHP=php8.3
LOCK="/tmp/deploy-$SITE.lock"

[ -d "$APP" ] || { echo "ERRO: $APP nao existe"; exit 1; }

# impede dois deploys simultaneos corrompendo vendor/ no meio do composer install
exec 9>"$LOCK"
flock -n 9 || { echo "Deploy de $SITE ja em andamento. Abortando."; exit 1; }

echo "==> Deploy de $SITE ($DOMAIN) — $(date '+%F %T')"
cd "$APP"

echo "--> modo de manutencao"
$PHP artisan down --retry=15 >/dev/null 2>&1 || true
# se qualquer passo falhar, o site SAI da manutencao em vez de ficar preso nela
trap 'cd "$APP" && '"$PHP"' artisan up >/dev/null 2>&1 || true' EXIT

# git como deploy, nao como root: as chaves e os aliases "Host github-<site>"
# vivem em /home/deploy/.ssh/config e os repos pertencem ao deploy. Como root o
# fetch funcionava apenas nos dois remotes HTTPS publicos (soavel e friedrich) e
# falhava nos outros sete com "Could not resolve hostname github-<site>".
# Rodando como deploy tambem dispensa o git config safe.directory.
echo "--> codigo"
sudo -u deploy git fetch origin main
sudo -u deploy git reset --hard origin/main
sudo -u deploy git log --oneline -1

echo "--> composer (PHP 8.3 explicito: o php default do servidor e 8.4)"
$PHP /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction

echo "--> migrations"
$PHP artisan migrate --force

echo "--> caches (umask 022: sem isto nascem 600 e o www-data nao le -> HTTP 500)"
umask 022
$PHP artisan config:clear && $PHP artisan cache:clear && $PHP artisan view:clear && $PHP artisan route:clear
$PHP artisan config:cache && $PHP artisan route:cache && $PHP artisan view:cache

echo "--> permissoes (DEPOIS dos caches — a ordem importa)"
chown -R deploy:www-data "$APP"
find "$APP/storage" "$APP/bootstrap/cache" -type d -exec chmod 2775 {} \;
find "$APP/storage" "$APP/bootstrap/cache" -type f -exec chmod 664 {} \;
chmod 640 "$APP/.env"

ILEGIVEIS=$(find "$APP/storage" "$APP/bootstrap/cache" -type f ! -perm -g=r | wc -l)
[ "$ILEGIVEIS" -eq 0 ] || { echo "ERRO: $ILEGIVEIS arquivos ilegiveis pelo grupo www-data"; exit 1; }

echo "--> reload php-fpm"
systemctl reload php8.3-fpm
sleep 2

echo "--> saindo da manutencao"
$PHP artisan up
trap - EXIT

echo "--> smoke test"
CODE=$(curl -so /dev/null -w '%{http_code}' --max-time 25 "https://$DOMAIN/")
echo "    home: HTTP $CODE"
[ "$CODE" = "200" ] || { echo "FALHOU: home retornou $CODE"; exit 1; }

echo "==> Deploy de $SITE concluido com sucesso"
