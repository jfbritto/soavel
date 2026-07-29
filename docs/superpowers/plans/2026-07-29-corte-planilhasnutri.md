# Corte do planilhasnutri — `planilhas.helpdiet.com.br`

**Data prevista:** madrugada de 30/07/2026
**Origem:** HostGator compartilhado, `108.167.132.218`, docroot `~/planilhas.helpdiet.com.br`
**Destino:** VPS `129.121.50.200`, `/var/www/planilhasnutri`
**Sensibilidade:** 10 clientes simultâneos em vários estados. É a migração mais sensível da série.

## Já pronto antes da janela

| Item | Estado |
|---|---|
| App no VPS | provisionado; login, planilha, **PDF**, escrita, upload e imagens validados no navegador |
| Pool FPM | `planilhasnutri`, `pm.max_children=10`, `php_value[memory_limit]=256M` (permite o `ini_set('512M')`) |
| nginx | `client_max_body_size 24M`, `fastcgi_read_timeout 120s` (alinhado ao `max_execution_time`) |
| Certificado | cobre `planilhas` **e** `www.planilhas`, vence 27/10/2026 |
| DNS | zona no Cloudflare, 44/44 conferida contra a origem; delegação trocada em 29/07 sem impacto |
| TTL | **60s** em `planilhas` e `www.planilhas` |
| E-mail | Resend por SMTP, envio testado e recebido; SPF/MX do apex intactos |
| Infra do app | sem cron (`schedule` comentado) e sem fila (`QUEUE_CONNECTION=sync`) — só nginx + FPM + MySQL |

**Tempos medidos** em ensaio: dump+transferência **3,1s** (28 MB gzip), restore **28,8s**, rsync do storage **2,7s** (731 arquivos, 152 MB). Janela realista: **menos de 5 minutos**.

## A regra que não se quebra

A origem entra em `artisan down` **antes** do dump e **permanece** em down durante a troca do A.

Com TTL 60, por até ~1 minuto depois da troca ainda há cliente resolvendo para o IP antigo. Se a origem estiver no ar nesse intervalo, ele grava no banco velho — e essa escrita se perde, porque o banco novo já foi restaurado. **É o único jeito de perder dado nesta migração**, e ele é evitado apenas pela ordem dos passos.

---

## Etapa 0 — Pré-checagem (antes de derrubar nada)

```bash
source /root/migracao/vars-planilhasnutri.sh

echo "── TTL 60 sendo servido por todos os resolvers?"
for NS in 8.8.8.8 1.1.1.1 9.9.9.9; do
  printf "   %-8s %s\n" "$NS" "$(dig +noall +answer A planilhas.helpdiet.com.br @$NS | awk '{print "ttl="$2"  ip="$5}')"
done

echo "── app do VPS de pé? (resolvendo local, antes do flip)"
curl -s --resolve planilhas.helpdiet.com.br:443:127.0.0.1 -o /dev/null \
     -w '   VPS  HTTP %{http_code} em %{time_total}s\n' https://planilhas.helpdiet.com.br/

echo "── origem de pé?"
curl -s -o /dev/null -w '   origem  HTTP %{http_code}\n' https://planilhas.helpdiet.com.br/

echo "── espaço em disco e backup de hoje"
df -h /var /root | tail -2
ls -lat /home/deploy/backups/ | head -6

echo "── QUEM ESTA DENTRO AGORA (o dado que abortou a tentativa de 29/07 as 18h)"
ssh -o BatchMode=yes -i /root/.ssh/id_migracao helpdi71@108.167.132.218 \
  'S=~/planilhas.helpdiet.com.br/storage/framework/sessions
   date "+   agora: %H:%M:%S"
   echo "   sessoes nos ultimos 5 min : $(find $S -type f -mmin -5 | wc -l)"
   echo "   sessoes nos ultimos 15 min: $(find $S -type f -mmin -15 | wc -l)"'
```

Só seguir se: TTL=60 nos três, VPS 200, origem 200, **e sessões dos últimos 5 min em 0**.

O `ls -lat` (ordenado por data) é deliberado: `ls -la | tail` ordena por nome, e como `uploads_*` vem depois de `mysql_*` no alfabeto, o `tail` esconde justamente os arquivos que interessam.

## Etapa 1 — Travar a origem

```bash
ssh -o BatchMode=yes -i /root/.ssh/id_migracao helpdi71@108.167.132.218 \
  'cd ~/planilhas.helpdiet.com.br && php artisan down --retry=60 && echo "  down OK"'

sleep 2
curl -s -o /dev/null -w '  origem agora: HTTP %{http_code}  (esperado 503)\n' \
  https://planilhas.helpdiet.com.br/
```

**A partir daqui o cliente vê página de manutenção.** O cronômetro começa.

## Etapa 2 — Fotografar o estado da origem

```bash
ssh -o BatchMode=yes -i /root/.ssh/id_migracao helpdi71@108.167.132.218 \
 'cd ~/planilhas.helpdiet.com.br
  unq() { sed -e "s/\r$//" -e "s/^\"//" -e "s/\"$//"; }
  DB=$(grep "^DB_DATABASE=" .env | cut -d= -f2- | unq)
  US=$(grep "^DB_USERNAME=" .env | cut -d= -f2- | unq)
  export MYSQL_PWD=$(grep "^DB_PASSWORD=" .env | cut -d= -f2- | unq)
  mysql -N -u "$US" "$DB" -e "
    SELECT table_name, table_rows FROM information_schema.tables
    WHERE table_schema=\"$DB\" ORDER BY table_name;"' \
  > /root/migracao/contagem-origem.txt

wc -l /root/migracao/contagem-origem.txt
```

## Etapa 3 — Dump e restore

```bash
time ssh -o BatchMode=yes -i /root/.ssh/id_migracao helpdi71@108.167.132.218 \
  '~/dump-tenant.sh planilhas.helpdiet.com.br' | gzip > /root/migracao/final-planilhasnutri.sql.gz

ls -lh /root/migracao/final-planilhasnutri.sql.gz
gzip -t /root/migracao/final-planilhasnutri.sql.gz && echo "  gzip íntegro"
zcat /root/migracao/final-planilhasnutri.sql.gz | tail -2   # deve terminar em "Dump completed"
```

```bash
mysql -e "DROP DATABASE IF EXISTS planilhasnutri;
          CREATE DATABASE planilhasnutri CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# DROP DATABASE não remove os privilégios do usuário, mas re-conceder é idempotente
# e barato — evita descobrir um "Access denied" com o app já no ar.
mysql -e "GRANT ALL PRIVILEGES ON planilhasnutri.* TO 'planilhasnutri'@'localhost'; FLUSH PRIVILEGES;"

time zcat /root/migracao/final-planilhasnutri.sql.gz | mysql planilhasnutri

mysql -N planilhasnutri -e "SELECT CONCAT('  tabelas: ',COUNT(*)) FROM information_schema.tables WHERE table_schema='planilhasnutri';"
```

## Etapa 4 — Storage e sessões

```bash
# storage/app/public/ e NAO storage/app/ — na origem nao existe storage/app/temp,
# e com --delete em storage/app/ o temp/invoices que o app precisa seria apagado.
time rsync -az --delete --info=stats2 -e "ssh -i /root/.ssh/id_migracao" \
  helpdi71@108.167.132.218:planilhas.helpdiet.com.br/storage/app/public/ \
  /var/www/planilhasnutri/storage/app/public/

# SESSION_DRIVER=file: sem isto os 10 clientes sao deslogados no corte.
# Mesmo APP_KEY, entao as sessoes descriptografam.
rsync -az -e "ssh -i /root/.ssh/id_migracao" \
  helpdi71@108.167.132.218:planilhas.helpdiet.com.br/storage/framework/sessions/ \
  /var/www/planilhasnutri/storage/framework/sessions/

# o rsync rodou como root: sem isto os arquivos nascem root e o www-data
# nao consegue gravar upload novo nem regravar sessao
chown -R deploy:www-data /var/www/planilhasnutri/storage
find /var/www/planilhasnutri/storage -type d -exec chmod 2775 {} \;
find /var/www/planilhasnutri/storage -type f -exec chmod 664 {} \;

echo "  ilegíveis pelo grupo: $(find /var/www/planilhasnutri/storage -type f ! -perm -g=r | wc -l)"
echo "  www-data grava em storage/app/public? $(sudo -u www-data test -w /var/www/planilhasnutri/storage/app/public && echo SIM || echo NAO)"
echo "  www-data grava em storage/fonts? $(sudo -u www-data test -w /var/www/planilhasnutri/storage/fonts && echo SIM || echo NAO)"
echo "  storage/app/temp sobreviveu? $(ls -d /var/www/planilhasnutri/storage/app/temp 2>/dev/null || echo AUSENTE)"
```

## Etapa 5 — Conferir ANTES de virar o DNS

```bash
cd /var/www/planilhasnutri
sudo -u deploy php8.3 artisan cache:clear

echo "── contagem por tabela: origem vs VPS"
mysql -N planilhasnutri -e "SELECT table_name, table_rows FROM information_schema.tables
  WHERE table_schema='planilhasnutri' ORDER BY table_name;" > /root/migracao/contagem-vps.txt
diff /root/migracao/contagem-origem.txt /root/migracao/contagem-vps.txt \
  && echo "   idênticas" || echo "   ^ diferenças (table_rows é estimativa no InnoDB; conferir as que importam)"

echo "── contagem exata nas tabelas que importam"
for T in users companies chains planilhas_visualizacoes documents produto_descartes; do
  printf "   %-26s %s\n" "$T" "$(mysql -N planilhasnutri -e "SELECT COUNT(*) FROM \`$T\`;" 2>/dev/null)"
done

echo "── o app responde com os dados novos?"
curl -s --resolve planilhas.helpdiet.com.br:443:127.0.0.1 -o /dev/null \
     -w '   HTTP %{http_code} em %{time_total}s\n' https://planilhas.helpdiet.com.br/

echo "── um arquivo real do storage serve por HTTP?"
F=$(find storage/app/public/empresas -type f -iname '*.png' | head -1)
REL=${F#storage/app/public/}
curl -s --resolve planilhas.helpdiet.com.br:443:127.0.0.1 -o /dev/null \
     -w "   HTTP %{http_code}  %{size_download} bytes\n" \
     "https://planilhas.helpdiet.com.br/storage/$REL"
echo "   em disco: $(stat -c %s "$F") bytes"
```

**Se algo aqui falhar, ainda não houve troca de DNS: dá `artisan up` na origem e nada aconteceu.**

## Etapa 6 — Virar o DNS

No Cloudflare, `helpdiet.com.br` → DNS → Records, editar **duas** linhas:

```
planilhas.helpdiet.com.br       A   108.167.132.218  ->  129.121.50.200
www.planilhas.helpdiet.com.br   A   108.167.132.218  ->  129.121.50.200
```

Manter `DNS only` (nuvem cinza) e TTL 60.

```bash
for i in $(seq 1 20); do
  printf "[%s] " "$(date '+%H:%M:%S')"
  for NS in 8.8.8.8 1.1.1.1 9.9.9.9; do
    printf "%s=%s  " "$NS" "$(dig +short A planilhas.helpdiet.com.br @$NS | head -1)"
  done
  printf "HTTP=%s\n" "$(curl -s -o /dev/null -w '%{http_code}' --max-time 8 https://planilhas.helpdiet.com.br/)"
  [ "$(dig +short A planilhas.helpdiet.com.br @8.8.8.8 | head -1)" = "129.121.50.200" ] && \
  [ "$(dig +short A planilhas.helpdiet.com.br @1.1.1.1 | head -1)" = "129.121.50.200" ] && \
  [ "$(dig +short A planilhas.helpdiet.com.br @9.9.9.9 | head -1)" = "129.121.50.200" ] && \
    { echo "  >>> propagado nos tres"; break; }
  sleep 15
done
```

## Etapa 7 — Validar no navegador

Com o DNS virado, remover a entrada do `/etc/hosts` do Mac (senão não se testa o caminho real):

```bash
sudo sed -i '' '/MIGRACAO-VPS-PLANILHASNUTRI/,+1d' /etc/hosts
sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder
```

No navegador, em `https://planilhas.helpdiet.com.br`: login, abrir planilha com histórico, **gerar PDF**, salvar uma planilha, subir arquivo, ver imagem.

Deixar rodando durante a validação:

```bash
tail -f /var/log/nginx/planilhasnutri-error.log \
        /var/log/php8.3-fpm-planilhasnutri.log \
        /var/log/php8.3-fpm-planilhasnutri-slow.log \
        /var/www/planilhasnutri/storage/logs/laravel.log
```

## Etapa 8 — Fechar

```bash
# renovacao automatica: os hooks manuais publicam no COMPARTILHADO e vao parar de
# funcionar agora que o dominio aponta para o VPS. Sem esta troca a renovacao
# quebra calada e so aparece em 60 dias.
cat /etc/letsencrypt/renewal/planilhas.helpdiet.com.br.conf

sed -i -e 's/^authenticator = manual/authenticator = nginx/' \
       -e '/^manual_auth_hook/d' -e '/^manual_cleanup_hook/d' -e '/^pref_challs/d' \
  /etc/letsencrypt/renewal/planilhas.helpdiet.com.br.conf

certbot renew --cert-name planilhas.helpdiet.com.br --dry-run
```

```bash
# normalizar a tabela migrations: 25 migrations cujo efeito ja esta no banco
# (schema criado a mao). Sem isto o 'artisan migrate --force' do deploy-tenant.sh
# aborta e nenhum deploy consegue concluir.
mysql planilhasnutri < /root/migracao/normalizar-migrations.sql
cd /var/www/planilhasnutri && sudo -u deploy php8.3 artisan migrate:status | \
  awk '$2=="No"{n++} $2=="Yes"{s++} END{print "  executadas: "s"  pendentes: "n}'
```

```bash
# backup imediato com o app ja em producao no VPS
/home/deploy/backup.sh && ls -lat /home/deploy/backups/ | head -6
```

**A origem fica em `down` de propósito.** Não dar `artisan up` — ela é a rede de segurança do rollback e não deve receber tráfego.

## Rollback

Válido enquanto nenhum cliente tiver gravado no VPS.

1. No Cloudflare, voltar os dois A para `108.167.132.218`
2. `ssh ... 'cd ~/planilhas.helpdiet.com.br && php artisan up'`
3. Confirmar HTTP 200 e propagação (TTL 60 → ~1 min)

O banco da origem não foi tocado em nenhum momento — só lido. Por isso o rollback é limpo.

## Depois do corte

- Remover `/etc/hosts` do Mac (na Etapa 7)
- Corrigir `config:cache` e `route:cache`: tirar `->name('login')` de `routes/web.php:57` (o `route('login')` já resolve para `/login`, comportamento não muda) e trocar o objeto `TelegramBotHandler` de `config/logging.php` por configuração serializável. Só depois disso o `deploy-tenant.sh` roda sem alteração.
- Converter `activity_log` e `produto_descartes` de `utf8mb3` para `utf8mb4` (não feito de propósito: 290 MB, lento, e fidelidade importava mais na janela)
- Decidir a retenção do `activity_log` (494 mil linhas, 290 MB = 89% do banco). Retenção sanitária é decisão de negócio.
- Arquivar a origem antes de cancelar o plano M
