#!/bin/bash
# =============================================================================
# deploy-static.sh — deploy de um site estatico no VPS
#
#   Uso:  deploy-static.sh <site> <dominio>
#   Ex.:  deploy-static.sh helpdiet helpdiet.com.br
#
# Para sites servidos direto pelo nginx, sem PHP. Diferente do deploy-tenant.sh:
# nao ha artisan down, composer, migrations nem caches — e portanto nao ha
# indisponibilidade. O nginx passa a servir os arquivos novos na requisicao
# seguinte ao reset.
#
# Rodar como root (faz chown).
# =============================================================================
set -euo pipefail

SITE="${1:?falta o nome do site}"
DOMAIN="${2:?falta o dominio}"
APP="/var/www/$SITE"
LOCK="/tmp/deploy-$SITE.lock"

[ -d "$APP" ] || { echo "ERRO: $APP nao existe"; exit 1; }

exec 9>"$LOCK"
flock -n 9 || { echo "Deploy de $SITE ja em andamento. Abortando."; exit 1; }

echo "==> Deploy de $SITE ($DOMAIN) — $(date '+%F %T')"

# git como deploy, nao como root: o repo pertence ao deploy, e o git recusa
# operar como root em repo de outro usuario. Rodando como root, o fetch tambem
# deixa .git/FETCH_HEAD com dono errado e o deploy seguinte falha com
# "Permission denied".
echo "--> codigo"
sudo -u deploy git -C "$APP" fetch origin main
sudo -u deploy git -C "$APP" reset --hard origin/main
sudo -u deploy git -C "$APP" log --oneline -1

echo "--> permissoes"
chown -R deploy:www-data "$APP"
find "$APP" -type d -not -path '*/.git/*' -exec chmod 755 {} \;
find "$APP" -type f -not -path '*/.git/*' -exec chmod 644 {} \;

ILEGIVEIS=$(find "$APP" -type f -not -path '*/.git/*' ! -perm -g=r | wc -l)
[ "$ILEGIVEIS" -eq 0 ] || { echo "ERRO: $ILEGIVEIS arquivos ilegiveis pelo grupo www-data"; exit 1; }

echo "--> smoke test"
CODE=$(curl -so /dev/null -w '%{http_code}' --max-time 25 "https://$DOMAIN/")
echo "    home: HTTP $CODE"
[ "$CODE" = "200" ] || { echo "FALHOU: home retornou $CODE"; exit 1; }

echo "==> Deploy de $SITE concluido"
