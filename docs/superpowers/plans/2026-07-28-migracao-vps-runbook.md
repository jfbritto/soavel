# Migração para VPS — Runbook de Execução

> **Modo de execução:** interativo. O usuário executa cada comando e cola a saída; o Claude valida contra a saída esperada antes de liberar o passo seguinte. Passos usam checkbox (`- [ ]`) para rastreamento.

**Goal:** Mover `soavelveiculos.com.br` e `friedrichveiculos.com.br` do compartilhado HostGator (conta `helpdi71`, IP `108.167.132.218`) para o VPS `129.121.50.200`, com o site público nunca fora do ar.

**Architecture:** Bare metal seguindo a convenção já estabelecida no VPS — nginx com um vhost por site, PHP 8.3-FPM com **pool dedicado por site** (resolve o teto de upload de 2M), MySQL 8 com banco e usuário próprios por site. Provisionamento completo e validado antes de qualquer mudança de DNS; virada feita bloqueando apenas o `/admin` na origem, o que congela a escrita sem derrubar o público.

**Tech Stack:** Ubuntu 22.04, nginx, PHP 8.3-FPM, MySQL 8.0.46, certbot (DNS-01 → HTTP-01), rsync, Laravel 8.

**Spec:** [2026-07-28-migracao-vps-hostgator-design.md](../specs/2026-07-28-migracao-vps-hostgator-design.md)

---

## Convenções deste runbook

**Cada passo tem:** comando exato → saída esperada → o que o Claude valida.

**Não avance sem validação.** Se a saída divergir do esperado, pare e cole o que apareceu.

> ⚠️ **Sempre `php8.3`, nunca `php`.** O `php` padrão do VPS é **8.4.18**, mas
> servimos estes sites em PHP 8.3-FPM — a versão que já roda em produção no
> compartilhado. Usar `php` puro faria o Composer resolver dependências e os
> caches de config/rotas serem gerados sob 8.4, divergindo do runtime que atende
> as requisições. Os scripts `deploy-masterveiculos.sh` e `deploy-taketicket.sh`
> já fixam `php8.4` pelo mesmo motivo. Todo comando artisan e composer deste
> runbook usa `php8.3` explicitamente.

> ⚠️ **Cuidado com `umask` na sessão.** Um `umask 077` solto vale para **todo
> arquivo criado depois dele naquele shell**, e `chown` não conserta — arquivos
> nascem `600` e o `www-data` não consegue ler. Use sempre em subshell:
> `( umask 077; comando )`. Arquivos que o PHP-FPM precisa ler exigem `chmod 644`
> explícito. Se aparecer `Permission denied ... Unable to open primary script`,
> a causa é essa.

> ⚠️ **`safe.directory` é necessário.** Os diretórios em `/var/www` pertencem a
> `deploy`, e o Git 2.35.2+ se recusa a operá-los como root
> (`fatal: detected dubious ownership`). Resolvemos de forma idempotente, sem o
> acúmulo dos scripts atuais (o gitconfig global já tem `taketicket` 15 vezes).

**Legenda de onde rodar:**
- 🟦 `[VPS]` — SSH como root em `129.121.50.200`
- 🟧 `[SHARED]` — cPanel → Terminal, ou SSH como `helpdi71`
- ⬜ `[MAC]` — seu terminal local
- 🌐 `[cPanel]` — interface web (Zone Editor)

**Variáveis por tenant.** As fases 1 e 2 rodam **duas vezes**, uma por site. Defina no início de cada sessão de shell:

```bash
# ── SOAVEL ────────────────────────────────
SITE=soavelveiculos
DOMAIN=soavelveiculos.com.br
SRC_DB=helpdi71_soavelveiculos

# ── FRIEDRICH ─────────────────────────────
SITE=friedrichveiculos
DOMAIN=friedrichveiculos.com.br
SRC_DB=helpdi71_friedrichveiculos
```

Faça o **Soavel inteiro primeiro** (é o maior: 34 veículos, 293 fotos, 104 MB). Ele valida o processo; o Friedrich vira repetição.

---

# FASE 0 — Ações imediatas

Independentes da migração. A 0.1 corrige um problema que já está acontecendo; a 0.2 é pré-requisito de prazo para a virada.

### Task 0.1: Desativar o cron órfão do Master

**Contexto:** `/home1/helpdi71/veiculos.helpflux.com.br` é cópia abandonada do Master (já migrado para o VPS). O cron roda `schedule:run` a cada 17 min contra `helpdi71_masterveiculos` (0,4 MB) enquanto o banco vivo é `masterveiculos` no VPS (1,6 MB). O Master integra com Asaas para cobrança.

- [ ] **Passo 1: 🟧 `[SHARED]` Guardar o crontab atual**

```bash
crontab -l > ~/crontab.backup.$(date +%F).txt && cat ~/crontab.backup.$(date +%F).txt
```

**Esperado:** as 3 linhas ativas (uma de `veiculos.helpflux`, duas de `beautymetrics`).
**Claude valida:** que o backup foi criado e o conteúdo corresponde ao inventário.

- [ ] **Passo 2: 🟧 `[SHARED]` Comentar apenas a linha do Master**

```bash
crontab -l | sed 's|^\(\*/17 .*veiculos\.helpflux.*\)$|# DESATIVADO 2026-07-28 (migrado p/ VPS): \1|' | crontab -
crontab -l
```

**Esperado:** a linha do `veiculos.helpflux` aparece comentada; as duas do `beautymetrics` **intactas e ativas**.
**Claude valida:** que só a linha alvo mudou e que o beautymetrics segue rodando.

---

### Task 0.2: Reduzir o TTL do DNS

**Contexto:** TTL atual de 14400 (4h). Precisa de 300 com pelo menos 48h de antecedência, para o valor antigo expirar em todos os resolvers antes da virada.

- [ ] **Passo 1: ⬜ `[MAC]` Registrar o estado atual**

```bash
for D in soavelveiculos.com.br friedrichveiculos.com.br; do
  echo "$D: $(dig +noall +answer A $D @8.8.8.8)"
done
```

**Esperado:** ambos com `14400 IN A 108.167.132.218`.

- [ ] **Passo 2: 🌐 `[cPanel]` Alterar o TTL**

cPanel → **Zone Editor** → cada domínio → **Manage**. No registro `A` do **apex** (nome = o domínio, sem `www`), trocar TTL `14400` → `300`. Salvar.

Faça nos **dois** domínios. **Não toque** em `mail`, `webmail`, `cpanel`, `ftp`, `autodiscover`, MX, TXT.

- [ ] **Passo 3: ⬜ `[MAC]` Confirmar a mudança na origem autoritativa**

```bash
for D in soavelveiculos.com.br friedrichveiculos.com.br; do
  echo "$D -> $(dig +noall +answer A $D @ns876.hostgator.com.br)"
done
```

**Esperado:** `300 IN A 108.167.132.218`.
**Claude valida:** TTL 300 nos dois. Consultamos o NS autoritativo direto porque o cache do 8.8.8.8 ainda serve 14400 por até 4h.

> ⏱️ **A partir daqui, esperar 48h antes da Fase 2.** A Fase 1 pode ser feita nesse intervalo.

---

# FASE 1 — Provisionar no VPS

Nada aqui afeta produção: os domínios continuam apontando para o compartilhado. Ao fim desta fase o site roda no VPS, acessível por `curl --resolve`.

### Task 1.1: Rede de segurança

- [ ] **Passo 1: 🟦 `[VPS]` Backup do estado atual do VPS**

```bash
mkdir -p /root/pre-migracao
mysqldump --all-databases --single-transaction --no-tablespaces | gzip > /root/pre-migracao/mysql-todos-$(date +%F_%H%M).sql.gz
tar czf /root/pre-migracao/nginx-php-$(date +%F_%H%M).tar.gz /etc/nginx /etc/php
ls -lh /root/pre-migracao/
```

**Esperado:** dois arquivos; o `.sql.gz` com ~500 KB (compatível com os backups diários existentes).
**Claude valida:** tamanhos plausíveis e ausência de erro.

- [ ] **Passo 2: 🟧 `[SHARED]` Backup da origem**

```bash
cd ~ && mkdir -p ~/pre-migracao
for DB in helpdi71_soavelveiculos helpdi71_friedrichveiculos; do
  mysqldump --single-transaction --no-tablespaces -u helpdi71_master -p "$DB" | gzip > ~/pre-migracao/$DB-$(date +%F).sql.gz
done
tar czf ~/pre-migracao/storage-soavel-$(date +%F).tar.gz -C ~/soavelveiculos.com.br storage/app
tar czf ~/pre-migracao/storage-friedrich-$(date +%F).tar.gz -C ~/friedrichveiculos.com.br storage/app
ls -lh ~/pre-migracao/
```

**Esperado:** 4 arquivos. Os `storage-*` com ~100 MB e ~70 MB. O `-p` sem senha faz o prompt aparecer — é intencional, evita a senha no histórico.
**Claude valida:** tamanhos coerentes com o inventário (104 MB e 73 MB).

- [ ] **Passo 3: 🟦 `[VPS]` Registrar a baseline dos sites existentes**

```bash
for U in https://helpflux.com.br https://veiculos.helpflux.com.br https://rocanossa.com.br https://taketicket.com.br https://treinaedu.com.br https://meet.treinaedu.com.br https://grafana.helpflux.com.br; do
  printf "%-40s %s\n" "$U" "$(curl -so /dev/null -w '%{http_code}' --max-time 15 $U)"
done
free -m | head -2
```

**Esperado:** todos 200/301/302. Guardar a linha de memória.
**Claude valida:** baseline registrada — é contra ela que checaremos regressão de coabitação no fim.

---

### Task 1.2: Verificar acesso ao repositório

**Contexto:** o remote é `https://github.com/jfbritto/soavel.git`. Se o repo for privado, HTTPS exige token e travaria o deploy automatizado.

- [ ] **Passo 1: 🟦 `[VPS]` Ver como os sites existentes autenticam**

```bash
for A in masterveiculos rocanossa taketicket treinaedu helpflux; do
  printf "%-16s %s\n" "$A" "$(git -C /var/www/$A remote get-url origin 2>/dev/null)"
done
ls -la /root/.ssh/ /home/deploy/.ssh/ 2>/dev/null | grep -E 'id_|config'
```

**Esperado:** revela se a convenção é SSH (`git@github.com:`) ou HTTPS com credential helper.
**Claude valida:** define se precisamos de deploy key. Se for SSH, reutilizamos a chave existente.

- [ ] **Passo 2: 🟦 `[VPS]` Testar clone efetivo**

```bash
cd /tmp && rm -rf teste-clone
git clone --depth 1 https://github.com/jfbritto/soavel.git teste-clone && echo "CLONE OK: $(git -C teste-clone log --oneline -1)"
rm -rf /tmp/teste-clone
```

**Esperado:** `CLONE OK: 436c8ea ...` (ou commit mais novo).
**Claude valida:** se pedir credencial, paramos e configuramos deploy key antes de seguir.

---

### Task 1.3: Estrutura e código

- [ ] **Passo 1: 🟦 `[VPS]` Definir as variáveis**

```bash
SITE=soavelveiculos
DOMAIN=soavelveiculos.com.br
SRC_DB=helpdi71_soavelveiculos
echo "SITE=$SITE DOMAIN=$DOMAIN SRC_DB=$SRC_DB"
```

**Esperado:** as três variáveis preenchidas.

> ⚠️ Se você abrir um novo SSH, **redefina as variáveis** — elas não persistem.

- [ ] **Passo 2: 🟦 `[VPS]` Confirmar que o diretório não existe**

```bash
ls -d /var/www/$SITE 2>/dev/null && echo "JÁ EXISTE — PARE" || echo "livre, pode seguir"
```

**Esperado:** `livre, pode seguir`.

- [ ] **Passo 3: 🟦 `[VPS]` Clonar**

```bash
git clone https://github.com/jfbritto/soavel.git /var/www/$SITE
git config --global --get-all safe.directory | grep -qx "/var/www/$SITE" \
  || git config --global --add safe.directory /var/www/$SITE
cd /var/www/$SITE && git log --oneline -1 && git status --short && echo "(status vazio acima = limpo)"
```

**Esperado:** commit `436c8ea` (ou mais novo) e `git status` vazio.
**Claude valida:** árvore limpa — nenhum `.htaccess` do cPanel veio junto. O `safe.directory` é adicionado só se ainda não existir.

- [ ] **Passo 4: 🟦 `[VPS]` Confirmar o binário do PHP 8.3 e do Composer**

```bash
ls -la /usr/bin/php8.3 && php8.3 -v | head -1
echo "--- php default (NAO usar): $(php -v | head -1)"
ls -la /usr/local/bin/composer 2>/dev/null || command -v composer
php8.3 -r 'foreach(["pdo_mysql","mbstring","openssl","tokenizer","xml","ctype","json","bcmath","fileinfo","gd","exif","zip","curl"] as $e) printf("%s:%s ", $e, extension_loaded($e)?"ok":"FALTA"); echo PHP_EOL;'
```

**Esperado:** `php8.3` presente em 8.3.x; o default aparecendo como 8.4.x; e **todas** as extensões `ok`.
**Claude valida:** qualquer `FALTA` bloqueia — instalamos a extensão antes de seguir. O `gd` e o `exif` são o que o `intervention/image` usa para redimensionar as fotos.

- [ ] **Passo 5: 🟦 `[VPS]` Instalar dependências de produção com PHP 8.3**

```bash
cd /var/www/$SITE
php8.3 /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -15
php8.3 -r 'require "vendor/autoload.php"; echo "autoload OK\n";'
```

**Esperado:** `Generating optimized autoload files` e `autoload OK`. Avisos de pacote abandonado são esperados (Laravel 8) e não bloqueiam.
**Claude valida:** ausência de erro de extensão ou de plataforma. Se o composer não estiver em `/usr/local/bin`, ajuste o caminho conforme o passo anterior.

---

### Task 1.4: Banco e usuário

- [ ] **Passo 1: 🟦 `[VPS]` Confirmar que o banco não existe**

```bash
mysql -e "SHOW DATABASES LIKE '$SITE';" && echo "--- (vazio acima = livre)"
```

**Esperado:** nenhuma linha de resultado.

- [ ] **Passo 2: 🟦 `[VPS]` Gerar senha e criar banco + usuário restrito**

> ⚠️ **O MySQL deste VPS tem `validate_password` ativo.** A política exige
> minúscula, maiúscula, dígito **e caractere especial**. Uma senha só
> alfanumérica é rejeitada com `ERROR 1819`. E como o cliente `mysql` em modo
> batch **para no primeiro erro**, um `CREATE USER` que falha impede o `GRANT`
> e o `FLUSH` de rodarem — o banco fica criado e o usuário não, o que se
> manifesta depois como `Access denied`.
>
> O sufixo `_1aZ` garante as quatro classes. Os caracteres especiais estão
> limitados a `_`, `-` e `.` de propósito: são seguros no shell, no
> `sed` do Task 1.7 e no `.env` sem precisar de aspas.

```bash
mysql -e "SHOW VARIABLES LIKE 'validate_password%';"

DB_PASS="$(openssl rand -base64 32 | tr -dc 'A-Za-z0-9' | head -c 26)_1aZ"
mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`$SITE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER '$SITE'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$SITE\`.* TO '$SITE'@'localhost';
FLUSH PRIVILEGES;
SQL

mkdir -p /root/migracao
# umask em subshell: fora dele o umask da sessão fica intacto
( umask 077; echo "$DB_PASS" > /root/migracao/$SITE-dbpass.txt )
chmod 600 /root/migracao/$SITE-dbpass.txt
echo "SENHA: $DB_PASS"
mysql -e "SELECT SCHEMA_NAME, DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='$SITE';"
```

Note o `IF NOT EXISTS`: torna o passo reexecutável se o `CREATE USER` falhar e for preciso repetir.

**Esperado:** `utf8mb4` / `utf8mb4_unicode_ci`, e a senha impressa.
**Claude valida:** charset correto. **Copie a senha** — ela vai para o `.env` no Task 1.7.

- [ ] **Passo 3: 🟦 `[VPS]` Verificar que o usuário só vê o próprio banco**

```bash
mysql -u $SITE -p"$DB_PASS" -e "SHOW DATABASES;"
```

**Esperado:** apenas `information_schema` e `$SITE`. **Não** deve aparecer `treinaedu`, `masterveiculos` etc.
**Claude valida:** isolamento efetivo — a correção de segurança da §3.5 do spec.

---

### Task 1.5: Canal de transferência

**Contexto:** vamos precisar de rsync duas vezes (inicial e final). Chave SSH do VPS para o compartilhado torna isso repetível e sem senha.

- [ ] **Passo 1: 🟦 `[VPS]` Gerar chave dedicada**

```bash
test -f /root/.ssh/id_migracao || ssh-keygen -t ed25519 -f /root/.ssh/id_migracao -N '' -C "migracao-vps-$(date +%F)"
cat /root/.ssh/id_migracao.pub
```

**Esperado:** a chave pública em uma linha começando com `ssh-ed25519`.

- [ ] **Passo 2: 🟧 `[SHARED]` Autorizar a chave**

Cole a chave pública do passo anterior no lugar de `CHAVE_AQUI`:

```bash
mkdir -p ~/.ssh && chmod 700 ~/.ssh
echo "CHAVE_AQUI" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
tail -1 ~/.ssh/authorized_keys
```

**Esperado:** a chave ecoada de volta.

- [ ] **Passo 3: 🟦 `[VPS]` Descobrir a porta SSH e testar**

HostGator compartilhado costuma usar 2222, não 22.

```bash
for P in 2222 22; do
  echo "-- porta $P:"
  ssh -o StrictHostKeyChecking=accept-new -o ConnectTimeout=10 -o BatchMode=yes \
      -i /root/.ssh/id_migracao -p $P helpdi71@108.167.132.218 \
      'echo CONECTOU; pwd; du -sh ~/soavelveiculos.com.br/storage/app' 2>&1 | head -5
done
```

**Esperado:** uma das portas retorna `CONECTOU`, `/home1/helpdi71` e `104M`.
**Claude valida:** qual porta funciona. Guarde em `SSH_PORT` para os próximos passos.

```bash
SSH_PORT=2222   # ajuste conforme o resultado
```

---

### Task 1.6: Migrar o banco

- [ ] **Passo 1a: 🟧 `[SHARED]` Criar o script de dump (uma vez, serve para os dois tenants e os dois dumps)**

O script lê as credenciais do próprio `.env` do tenant e usa `MYSQL_PWD`, então **a senha nunca aparece** num comando que eu escrevo, no histórico do shell do VPS, nem na lista de processos (`-p` na linha de comando apareceria em `ps`).

```bash
cat > ~/dump-tenant.sh <<'SCRIPT'
#!/bin/bash
# Uso: ~/dump-tenant.sh <diretorio-do-tenant>
# Ex.:  ~/dump-tenant.sh soavelveiculos.com.br
set -euo pipefail
cd "$HOME/$1"
unq() { sed -e 's/\r$//' -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'\$//"; }
DB=$(grep '^DB_DATABASE=' .env | head -1 | cut -d= -f2- | unq)
US=$(grep '^DB_USERNAME=' .env | head -1 | cut -d= -f2- | unq)
MYSQL_PWD=$(grep '^DB_PASSWORD=' .env | head -1 | cut -d= -f2- | unq)
export MYSQL_PWD
mysqldump --single-transaction --no-tablespaces --routines --triggers -u "$US" "$DB"
SCRIPT
chmod 700 ~/dump-tenant.sh

# testar localmente antes de usar via SSH
~/dump-tenant.sh soavelveiculos.com.br | head -3
~/dump-tenant.sh soavelveiculos.com.br | grep -c 'CREATE TABLE'
```

**Esperado:** o cabeçalho do mysqldump e **18** tabelas.
**Claude valida:** se o script funciona local, funciona via SSH.

- [ ] **Passo 1b: 🟦 `[VPS]` Dump da origem direto para o VPS**

```bash
SSH_PORT=22
SSH_OPTS="-o BatchMode=yes -i /root/.ssh/id_migracao -p $SSH_PORT"
REMOTE=helpdi71@108.167.132.218

mkdir -p /root/migracao
ssh $SSH_OPTS $REMOTE "~/dump-tenant.sh $DOMAIN" > /root/migracao/$SITE-inicial.sql
ls -lh /root/migracao/$SITE-inicial.sql
grep -c 'CREATE TABLE' /root/migracao/$SITE-inicial.sql
tail -2 /root/migracao/$SITE-inicial.sql
```

**Esperado:** **18 tabelas** (`CREATE TABLE`) e a última linha `-- Dump completed on ...`.
**Claude valida:** a linha `Dump completed` é a prova de que o dump não truncou. Sem ela, refazer.

> São 18 tabelas, não 19: dos 19 arquivos de migration, `add_troca_to_sales` e
> `add_environment_to_billing_history` são `ALTER`, não `CREATE`. 17 `CREATE TABLE`
> mais a tabela `migrations` = 18. Já `migrate:status` (Task 1.7) mostra **19**
> migrations, porque conta os arquivos. Os dois números estão certos.

> A senha do banco de origem está no `.env` do compartilhado (`DB_PASSWORD`). Substitua `SENHA_DO_BANCO_ORIGEM`.

- [ ] **Passo 2: 🟦 `[VPS]` Restaurar**

```bash
mysql $SITE < /root/migracao/$SITE-inicial.sql
mysql $SITE -e "SELECT COUNT(*) AS tabelas FROM information_schema.tables WHERE table_schema='$SITE';"
mysql $SITE -e "SELECT 'vehicles' t,COUNT(*) n FROM vehicles UNION ALL SELECT 'photos',COUNT(*) FROM vehicle_photos UNION ALL SELECT 'customers',COUNT(*) FROM customers UNION ALL SELECT 'sales',COUNT(*) FROM sales UNION ALL SELECT 'leads',COUNT(*) FROM leads UNION ALL SELECT 'users',COUNT(*) FROM users UNION ALL SELECT 'settings',COUNT(*) FROM settings;"
```

**Esperado (Soavel):** 18 tabelas; 34 veículos, 293 fotos, 18 clientes, 19 vendas, 3 leads, 1 usuário.
**Esperado (Friedrich):** 18 tabelas; **≥** 6 veículos e **≥** 82 fotos, 5 clientes, 3 vendas, 3 leads, 1 usuário.
**Claude valida:** contagens comparadas com a origem **medida no momento do dump**, não com o inventário de 28/07 09:30.

> ⚠️ **O Friedrich está sendo editado ativamente.** Entre 09:30 e 10:34 de 28/07
> ganhou 19 arquivos novos em `storage/app` (209 → 228) e provavelmente linhas
> novas em `vehicle_photos`. Não trate os números do inventário como fixos: meça
> a origem imediatamente antes de cada dump e compare com isso.

- [ ] **Passo 3: 🟦 `[VPS]` Confirmar o charset das tabelas restauradas**

```bash
mysql -e "SELECT DISTINCT table_collation FROM information_schema.tables WHERE table_schema='$SITE';"
mysql -e "SELECT table_name, table_collation FROM information_schema.tables WHERE table_schema='$SITE' AND table_collation <> 'utf8mb4_unicode_ci';"
echo "(segunda consulta vazia = todas as tabelas corretas)"
```

**Esperado:** linha única `utf8mb4_unicode_ci`, e a segunda consulta **vazia**.
**Claude valida:** nenhuma conversão necessária.

> ✅ **Verificado em 28/07 no Soavel:** as 18 tabelas chegaram como
> `utf8mb4_unicode_ci`. As migrations do Laravel criam as tabelas com o charset
> do `config/database.php` (`utf8mb4` / `utf8mb4_unicode_ci`), independentemente
> do `collation_server` do servidor de origem — que no compartilhado é
> `utf8_unicode_ci` e me levou a prever conversão erradamente.
>
> **Não há passo de `ALTER TABLE CONVERT` neste runbook.** Se em algum tenant
> futuro a segunda consulta acima retornar linhas, aí sim gere e rode a
> conversão — mas meça antes de converter.

- [ ] **Passo 5: 🟦 `[VPS]` Verificar integridade de acentuação**

```bash
mysql $SITE -e "SELECT id, LEFT(name,40) FROM vehicles WHERE name REGEXP '[ãáâàéêíóôõúüç]' LIMIT 5;"
mysql $SITE -e "SELECT \`key\`, LEFT(value,50) FROM settings WHERE value REGEXP '[ãáâàéêíóôõúüç]' LIMIT 5;"
```

**Esperado:** acentos corretos, sem `Ã£` nem `?`.
**Claude valida:** ausência de mojibake — o risco real de conversão de charset.

---

### Task 1.7: Arquivos e configuração

- [ ] **Passo 1: 🟦 `[VPS]` Copiar o `.env`, preservando o `APP_KEY`**

```bash
ssh -i /root/.ssh/id_migracao -p $SSH_PORT helpdi71@108.167.132.218 \
  "cat ~/$DOMAIN/.env" > /var/www/$SITE/.env
grep -c . /var/www/$SITE/.env
grep -E '^(APP_NAME|APP_ENV|APP_DEBUG|APP_URL|DB_)' /var/www/$SITE/.env
```

**Esperado:** o `.env` da origem, com `APP_KEY` presente.
**Claude valida:** `APP_KEY` copiada. Trocá-la invalidaria sessões e qualquer dado cifrado.

- [ ] **Passo 2: 🟦 `[VPS]` Ajustar as chaves que mudam**

Use a senha gerada no Task 1.4:

```bash
cd /var/www/$SITE
cp .env .env.origem-backup
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$SITE|" .env
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$SITE|" .env
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=SENHA_GERADA_NO_TASK_1_4|" .env
sed -i "s|^DB_HOST=.*|DB_HOST=127.0.0.1|" .env
sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" .env
sed -i "s|^MAIL_MAILER=.*|MAIL_MAILER=log|" .env
grep -E '^(APP_URL|APP_DEBUG|DB_DATABASE|DB_USERNAME|DB_HOST|MAIL_MAILER)' .env
```

**Esperado:** `APP_URL=https://soavelveiculos.com.br` **sem barra final** (correção da §5), `APP_DEBUG=false`, `MAIL_MAILER=log`.
**Claude valida:** ausência de barra final e `APP_DEBUG=false`.

- [ ] **Passo 3: 🟦 `[VPS]` Testar a conexão com o banco pelo app**

```bash
cd /var/www/$SITE && php8.3 artisan migrate:status 2>&1 | tail -8
```

**Esperado:** as 19 migrations listadas como `Ran`. Nenhuma pendente.
**Claude valida:** app conversa com o banco e o schema está completo.

- [ ] **Passo 4: 🟦 `[VPS]` Transferir o `storage/app`**

```bash
rsync -az --info=stats2 -e "ssh -i /root/.ssh/id_migracao -p $SSH_PORT" \
  helpdi71@108.167.132.218:~/$DOMAIN/storage/app/ /var/www/$SITE/storage/app/
echo "--- arquivos: $(find /var/www/$SITE/storage/app -type f | wc -l)"
du -sh /var/www/$SITE/storage/app
ls /var/www/$SITE/storage/app
du -sh /var/www/$SITE/storage/app/public/*/
```

**Esperado (Soavel):** 310 arquivos, ~101 MB reais, com `public/`, `customer-documents/`, `vehicle-documents/`.
**Esperado (Friedrich):** **≥ 228** arquivos, ~83 MB reais, com `public/`, `vehicle-documents/`.
**Claude valida:** contagem contra a origem medida no momento, e presença dos diretórios privados — os documentos de cliente **não** podem ficar dentro de `public/`.

Para medir a origem na hora, em vez de confiar em número anotado:

```bash
ssh -i /root/.ssh/id_migracao -p $SSH_PORT helpdi71@108.167.132.218 \
  "find ~/$DOMAIN/storage/app -type f | wc -l"
```

Rode antes e depois do rsync — os dois números têm de coincidir.

- [ ] **Passo 5: 🟦 `[VPS]` Symlink e permissões**

```bash
cd /var/www/$SITE
rm -f public/storage && php8.3 artisan storage:link
chown -R deploy:www-data /var/www/$SITE
find /var/www/$SITE -type d -not -path '*/.git/*' -exec chmod 755 {} \;
find /var/www/$SITE -type f -not -path '*/.git/*' -exec chmod 644 {} \;
chmod -R 775 storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod g+s {} \;
chmod 640 .env && chown deploy:www-data .env
ls -la public/storage && stat -c '%U:%G %a' . storage bootstrap/cache .env
```

**Esperado:** symlink `public/storage -> /var/www/soavelveiculos/storage/app/public`; `deploy:www-data`; `775` em storage e bootstrap/cache; `640` no `.env`.
**Claude valida:** o FPM roda como `www-data`, então precisa de escrita em `storage` via grupo; o setgid mantém isso em arquivos novos.

- [ ] **Passo 6: 🟦 `[VPS]` Gerar caches de produção**

```bash
cd /var/www/$SITE
umask 022    # obrigatório: sem isso os caches nascem 600 e o www-data não os lê

php8.3 artisan config:clear && php8.3 artisan cache:clear && php8.3 artisan view:clear && php8.3 artisan route:clear
php8.3 artisan config:cache && php8.3 artisan route:cache && php8.3 artisan view:cache

# permissões DEPOIS de gerar — esta é a ordem que importa
chown -R deploy:www-data storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 2775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;

echo "arquivos ilegíveis pelo grupo (tem de ser 0): $(find storage bootstrap/cache -type f ! -perm -g=r | wc -l)"
sudo -u www-data test -r bootstrap/cache/config.php && echo "www-data lê o config cache: OK"
ls -la bootstrap/cache/
```

**Esperado:** `config.php` e `routes-v7.php` criados, **zero** arquivos ilegíveis pelo grupo, e `www-data lê o config cache: OK`.

> ⚠️ **A ordem é o que importa.** Gerar cache e *depois* ajustar permissão. Se
> você rodar `chmod -R 775` antes de `config:cache`, os arquivos gerados em
> seguida nascem com o umask da sessão — e se ele for restritivo, o site retorna
> **500** com `ReflectionException: Class ` (nome de classe vazio), porque o
> Laravel lê um cache de config que não consegue abrir. O `laravel.log` fica
> vazio nesse caso: a falha acontece antes do logger existir, então o erro só
> aparece em `/var/log/nginx/<site>-error.log`.
>
> Diagnóstico em um comando:
> `sudo -u www-data test -r bootstrap/cache/config.php && echo OK || echo 'NAO LEGIVEL'`

**Claude valida:** limpar antes de gerar também é essencial — caches da origem carregam caminhos absolutos `/home1/helpdi71/...`.

---

### Task 1.8: Pool PHP-FPM dedicado

**Contexto:** o VPS tem `upload_max_filesize = 2M` global sem override. Fotos de veículo têm 3–8 MB. Este é **o bloqueador** identificado no spec.

- [ ] **Passo 1: 🟦 `[VPS]` Confirmar o problema**

```bash
grep -E '^(upload_max_filesize|post_max_size|memory_limit)' /etc/php/8.3/fpm/php.ini
ls /etc/php/8.3/fpm/pool.d/
```

**Esperado:** `2M` / `8M` / `128M`, e apenas `www.conf`.
**Claude valida:** confirma o teto e que ainda não há pool por site.

- [ ] **Passo 2: 🟦 `[VPS]` Criar o pool**

```bash
cat > /etc/php/8.3/fpm/pool.d/$SITE.conf <<EOF
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
EOF
cat /etc/php/8.3/fpm/pool.d/$SITE.conf
```

**Esperado:** o arquivo com `[soavelveiculos]` e o socket com o nome do site.
**Claude valida:** nome do pool, socket e limites. `ondemand` não mantém processo ocioso — decisão pela RAM apertada.

- [ ] **Passo 3: 🟦 `[VPS]` Validar a sintaxe SEM reiniciar**

```bash
php-fpm8.3 -t
```

**Esperado:** `configuration file /etc/php/8.3/fpm/php-fpm.conf test is successful`.
**Claude valida:** obrigatório antes do reload — erro de sintaxe aqui derrubaria o pool compartilhado e, com ele, `rocanossa` e `treinaedu`.

- [ ] **Passo 4: 🟦 `[VPS]` Recarregar e conferir**

```bash
systemctl reload php8.3-fpm
sleep 2
systemctl is-active php8.3-fpm
ls -la /run/php/php8.3-fpm-$SITE.sock
curl -so /dev/null -w 'rocanossa: %{http_code}\n' --max-time 15 https://rocanossa.com.br
curl -so /dev/null -w 'treinaedu: %{http_code}\n' --max-time 15 https://treinaedu.com.br
```

**Esperado:** `active`, o socket existe, e os dois sites do PHP 8.3 continuam respondendo 200/301.
**Claude valida:** regressão de coabitação — `reload` não deveria afetar os vizinhos, e confirmamos.

---

### Task 1.9: Vhost nginx (HTTP)

- [ ] **Passo 1: 🟦 `[VPS]` Criar o vhost, espelhando a convenção do `masterveiculos`**

```bash
cat > /etc/nginx/sites-available/$SITE <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;
    root /var/www/$SITE/public;
    index index.php;

    charset utf-8;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

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
EOF
grep -E 'server_name|root|fastcgi_pass|client_max_body_size' /etc/nginx/sites-available/$SITE
```

**Esperado:** `server_name` com apex e www, root em `/public`, `fastcgi_pass` no socket do site, `client_max_body_size 24M`.
**Claude valida:** `client_max_body_size` (24M) tem de ser ≥ `post_max_size` do PHP (24M), senão o nginx corta antes.

- [ ] **Passo 2: 🟦 `[VPS]` Habilitar e testar**

```bash
ln -sfn /etc/nginx/sites-available/$SITE /etc/nginx/sites-enabled/$SITE
nginx -t
```

**Esperado:** `syntax is ok` e `test is successful`.
**Claude valida:** **não recarregue se falhar** — derrubaria os sete sites em produção.

- [ ] **Passo 3: 🟦 `[VPS]` Recarregar e validar a baseline**

```bash
systemctl reload nginx
for U in https://helpflux.com.br https://veiculos.helpflux.com.br https://rocanossa.com.br https://taketicket.com.br https://treinaedu.com.br https://meet.treinaedu.com.br https://grafana.helpflux.com.br; do
  printf "%-40s %s\n" "$U" "$(curl -so /dev/null -w '%{http_code}' --max-time 15 $U)"
done
```

**Esperado:** códigos idênticos aos do Task 1.1 Passo 3.
**Claude valida:** comparação direta com a baseline.

- [ ] **Passo 4: 🟦 `[VPS]` Primeiro acesso ao app pelo próprio VPS**

```bash
curl -s --resolve $DOMAIN:80:127.0.0.1 http://$DOMAIN/ -o /tmp/home.html -w 'HTTP %{http_code}  bytes %{size_download}\n'
grep -oE '<title>[^<]*</title>' /tmp/home.html
grep -c 'estoque' /tmp/home.html
```

**Esperado:** `HTTP 200`, título do tenant, e ocorrências de `estoque`.
**Claude valida:** app renderizando do VPS. Se vier 500, o próximo passo mostra o motivo.

- [ ] **Passo 5: 🟦 `[VPS]` Conferir os logs (mesmo com sucesso)**

```bash
tail -20 /var/www/$SITE/storage/logs/laravel.log 2>/dev/null || echo "(sem log — bom sinal)"
tail -10 /var/log/php8.3-fpm-$SITE.log 2>/dev/null || echo "(sem erro no pool)"
tail -10 /var/log/nginx/$SITE-error.log
```

**Esperado:** sem exceção.
**Claude valida:** erros silenciosos aparecem aqui antes de virarem incidente.

---

### Task 1.10: Certificado por DNS-01

**Contexto:** queremos o certificado **antes** da virada, para não haver um segundo de aviso de certificado no navegador dos visitantes.

> ✅ **Método validado em 28/07: HTTP-01 publicando no compartilhado via hook.**
>
> O domínio ainda aponta para o compartilhado, e temos SSH para lá. Então o
> desafio HTTP-01 é publicado **no compartilhado** por um script, que verifica a
> URL pública antes de liberar a validação. Vantagens sobre o DNS-01 manual:
> criar arquivo é instantâneo (sem TTL nem propagação), é não interativo (imune
> a queda de conexão SSH) e a verificação embutida evita queimar tentativas
> contra o limite da Let's Encrypt.
>
> **Duas armadilhas descobertas na prática:**
>
> 1. **O Document Root do compartilhado é a pasta do domínio, não `public/`.** O
>    `.htaccess` da raiz reescreve tudo para `public/`, e o desafio criado só em
>    `public/.well-known/` retornava **404** (a requisição caía no Laravel). O
>    hook publica nos **dois** locais, o que resolve sem depender de descobrir
>    qual é o correto.
> 2. **O mod_security da HostGator devolve 406 para `User-Agent` simplista** como
>    `Mozilla/5.0`. O UA real da Let's Encrypt **não** é bloqueado. Se você testar
>    o desafio com curl, use o UA real, senão terá falso negativo.

- [ ] **Passo 1a: 🟦 `[VPS]` Criar os hooks (uma vez, serve para os dois tenants)**

```bash
mkdir -p /root/migracao

cat > /root/migracao/acme-auth.sh <<'HOOK'
#!/bin/bash
# certbot fornece: CERTBOT_DOMAIN, CERTBOT_TOKEN, CERTBOT_VALIDATION
set -euo pipefail
case "$CERTBOT_DOMAIN" in
  www.*) DIR="${CERTBOT_DOMAIN#www.}" ;;
  *)     DIR="$CERTBOT_DOMAIN" ;;
esac
SSH="ssh -o BatchMode=yes -o ConnectTimeout=15 -i /root/.ssh/id_migracao -p 22 helpdi71@108.167.132.218"
LE_UA="Mozilla/5.0 (compatible; Let's Encrypt validation server; +https://www.letsencrypt.org)"
$SSH "bash -s" <<REMOTE
set -e
for B in "\$HOME/$DIR" "\$HOME/$DIR/public"; do
  mkdir -p "\$B/.well-known/acme-challenge"
  chmod 755 "\$B/.well-known" "\$B/.well-known/acme-challenge"
  printf '%s' '$CERTBOT_VALIDATION' > "\$B/.well-known/acme-challenge/$CERTBOT_TOKEN"
  chmod 644 "\$B/.well-known/acme-challenge/$CERTBOT_TOKEN"
done
REMOTE
URL="http://$CERTBOT_DOMAIN/.well-known/acme-challenge/$CERTBOT_TOKEN"
for i in $(seq 1 10); do
  GOT=$(curl -sSL --max-time 15 -A "$LE_UA" "$URL" 2>/dev/null || true)
  if [ "$GOT" = "$CERTBOT_VALIDATION" ]; then
    echo "  [hook] desafio publicado e verificado: $CERTBOT_DOMAIN"; exit 0
  fi
  sleep 2
done
echo "  [hook] FALHOU: $URL nao devolveu o valor esperado" >&2
exit 1
HOOK

cat > /root/migracao/acme-cleanup.sh <<'HOOK'
#!/bin/bash
set -euo pipefail
case "$CERTBOT_DOMAIN" in
  www.*) DIR="${CERTBOT_DOMAIN#www.}" ;;
  *)     DIR="$CERTBOT_DOMAIN" ;;
esac
SSH="ssh -o BatchMode=yes -o ConnectTimeout=15 -i /root/.ssh/id_migracao -p 22 helpdi71@108.167.132.218"
$SSH "rm -f ~/$DIR/.well-known/acme-challenge/$CERTBOT_TOKEN ~/$DIR/public/.well-known/acme-challenge/$CERTBOT_TOKEN" || true
echo "  [cleanup] removido: $CERTBOT_DOMAIN"
HOOK

chmod 700 /root/migracao/acme-auth.sh /root/migracao/acme-cleanup.sh
bash -n /root/migracao/acme-auth.sh && bash -n /root/migracao/acme-cleanup.sh && echo "SINTAXE OK"
```

- [ ] **Passo 1b: 🟦 `[VPS]` Emitir, de forma não interativa**

Rode dentro de `screen` — se a conexão cair no meio de um certbot interativo, o processo fica **órfão e vivo**, travado num prompt sem stdin, e segura os locks em `/etc/letsencrypt`. Aí a próxima tentativa falha com `Another instance of Certbot is already running`, e a saída é `kill <pid>` antes de remover locks (**nunca** remova locks com o processo vivo).

```bash
command -v screen >/dev/null || apt-get install -y screen
screen -S cert

certbot certonly --manual --preferred-challenges http \
  --manual-auth-hook /root/migracao/acme-auth.sh \
  --manual-cleanup-hook /root/migracao/acme-cleanup.sh \
  --agree-tos --no-eff-email --non-interactive \
  -d $DOMAIN -d www.$DOMAIN
echo "exit: $?"
```

**Esperado:** duas linhas `[hook] desafio publicado e verificado`, duas de `[cleanup] removido`, e `Successfully received certificate`.

> ⚠️ **A renovação automática deste certificado vai FALHAR depois do cutover.** O
> `renewal/*.conf` fica com `authenticator = manual` apontando para um hook que
> publica no compartilhado — mas após a virada a Let's Encrypt busca o desafio no
> VPS. Por isso o Task 3.1 reemite via `--nginx` logo depois da virada. Não pule.

O certbot vai **pausar duas vezes**, uma por domínio, pedindo um registro TXT diferente para cada:

| Domínio validado | Nome do registro TXT | Name no cPanel Zone Editor |
|---|---|---|
| `$DOMAIN` | `_acme-challenge.$DOMAIN` | `_acme-challenge` |
| `www.$DOMAIN` | `_acme-challenge.www.$DOMAIN` | `_acme-challenge.www` |

> ⚠️ **São dois nomes distintos, não dois valores no mesmo nome.** O desafio
> DNS-01 coloca o TXT em `_acme-challenge.<domínio-validado>`, e `www.$DOMAIN` é
> um domínio diferente do apex. Errar isso faz a validação falhar — e a Let's
> Encrypt limita tentativas falhas, então vale acertar de primeira.

**Claude valida:** os valores TXT antes de você criar, e a propagação antes de você continuar.

- [ ] **Passo 2: 🟦 `[VPS]` Confirmar a emissão**

```bash
certbot certificates --cert-name $DOMAIN
```

**Esperado:** `Domains: soavelveiculos.com.br www.soavelveiculos.com.br` e validade de ~90 dias.
**Claude valida:** ambos os nomes no certificado.

- [ ] **Passo 5: 🟦 `[VPS]` Adicionar HTTPS ao vhost**

Reescrevemos o vhost inteiro em vez de editar com `sed`/`python` — edição de config nginx por substituição de texto é frágil e o custo de regerar é zero:

```bash
cp /etc/nginx/sites-available/$SITE /root/migracao/$SITE-vhost-http.bak
cat > /etc/nginx/sites-available/$SITE <<EOF
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name $DOMAIN www.$DOMAIN;
    root /var/www/$SITE/public;
    index index.php;

    charset utf-8;

    ssl_certificate     /etc/letsencrypt/live/$DOMAIN/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/$DOMAIN/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

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

server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;
    return 301 https://\$host\$request_uri;
}
EOF
nginx -t && grep -E 'listen|ssl_certificate |return 301|fastcgi_pass' /etc/nginx/sites-available/$SITE
```

**Esperado:** `test is successful`, bloco 443 com o certificado e bloco 80 redirecionando.
**Claude valida:** sintaxe e presença do redirect.

- [ ] **Passo 6: 🟦 `[VPS]` Recarregar e testar HTTPS**

```bash
systemctl reload nginx
curl -s --resolve $DOMAIN:443:127.0.0.1 --resolve $DOMAIN:80:127.0.0.1 \
  https://$DOMAIN/ -o /dev/null -w 'HTTPS %{http_code}\n'
curl -s --resolve $DOMAIN:80:127.0.0.1 -o /dev/null -w 'HTTP->%{http_code} %{redirect_url}\n' http://$DOMAIN/
```

**Esperado:** `HTTPS 200` e `HTTP->301 https://...`.
**Claude valida:** HTTPS servindo e redirect ativo — pronto para a virada.

---

### Task 1.11: Validação funcional completa

**Contexto:** o checklist da §6 do spec, executado **antes** de mexer no DNS. É o go/no-go da Fase 2.

- [ ] **Passo 1: ⬜ `[MAC]` Apontar seu navegador para o VPS via `/etc/hosts`**

```bash
sudo sh -c 'echo "129.121.50.200 soavelveiculos.com.br www.soavelveiculos.com.br" >> /etc/hosts'
dscacheutil -flushcache; sudo killall -HUP mDNSResponder
ping -c1 soavelveiculos.com.br | head -1
```

**Esperado:** o ping mostra `129.121.50.200`.
**Claude valida:** só a **sua** máquina passa a ver o VPS. O DNS público segue intocado.

> ⚠️ **Anote para desfazer depois:** essa linha precisa sair do `/etc/hosts` no Task 3.1, senão você continuará vendo o VPS por engano mesmo após a virada, e não perceberá problemas de DNS.

- [ ] **Passo 2: ⬜ `[MAC]` Site público no navegador**

Abra `https://soavelveiculos.com.br` e confirme:

- [ ] Home carrega com logo e favicon corretos (vêm de `storage/app/public/settings`)
- [ ] `/estoque` lista o número de veículos **`disponivel`** — não o total
- [ ] Uma página de veículo abre pelo slug, galeria completa, thumbs aparecem
- [ ] `/sitemap.xml` e `/robots.txt` respondem
- [ ] Formulário de contato envia sem erro

> ⚠️ **O site público filtra por status.** `Site\VehicleController::index` usa
> `Vehicle::disponivel()`, que é `where('status','disponivel')`. No Soavel há 34
> veículos no banco mas só **16** disponíveis (14 vendidos, 4 reservados) — o
> `/estoque` mostra 16. Meça antes do teste visual, em vez de comparar com o
> total:
>
> ```bash
> mysql $SITE -e "SELECT status, COUNT(*) n FROM vehicles GROUP BY status;"
> mysql $SITE -e "SELECT COUNT(*) AS aparece_no_estoque FROM vehicles WHERE status='disponivel';"
> mysql $SITE -e "SELECT COUNT(*) AS aparece_na_home FROM vehicles WHERE status='disponivel' AND destaque=1;"
> ```
>
> A home mostra até 6 dos `disponivel` **e** `destaque`.

**Claude valida:** o número de veículos contra a consulta acima, e o carregamento das imagens confirma banco e symlink juntos.

- [ ] **Passo 3: ⬜ `[MAC]` Admin — e o teste que importa mais**

Acesse `/admin` e confirme:

- [ ] Login funciona com o usuário existente (a senha é a mesma — `APP_KEY` e hash preservados)
- [ ] Listagens batem: 34 veículos, 18 clientes, 19 vendas, 3 leads
- [ ] 🎯 **Upload de foto de veículo com arquivo de ~6 MB** — este é o teste do bloqueador
- [ ] Thumb gerada e visível na listagem
- [ ] Excluir a foto de teste remove arquivo e thumb
- [ ] Upload e download de documento de cliente (valida `storage/app` privado)
- [ ] Salvar Configurações reflete no site público

**Claude valida:** se o upload de 6 MB passar, o pool FPM está correto e o risco maior do plano está eliminado.

- [ ] **Passo 4: 🟦 `[VPS]` Conferir logs e memória após os testes**

```bash
tail -30 /var/www/$SITE/storage/logs/laravel.log 2>/dev/null | grep -iE 'error|exception' || echo "sem erro no laravel.log"
tail -10 /var/log/php8.3-fpm-$SITE.log 2>/dev/null || echo "sem erro no pool"
free -m | head -2
ls -la /var/www/$SITE/storage/app/public/vehicles/ | tail -5
```

**Esperado:** sem exceção; memória disponível ainda saudável; os arquivos novos do teste presentes.
**Claude valida:** compara a memória com a baseline do Task 1.1.

> 🚦 **GO/NO-GO.** Todos os itens acima passando = liberado para a Fase 2. Qualquer falha = corrigir aqui, onde não há impacto em produção.

---

### Task 1.12: Repetir para o Friedrich

- [ ] **Passo 1: 🟦 `[VPS]` Redefinir as variáveis e refazer os Tasks 1.3 a 1.11**

```bash
SITE=friedrichveiculos
DOMAIN=friedrichveiculos.com.br
SRC_DB=helpdi71_friedrichveiculos
echo "SITE=$SITE DOMAIN=$DOMAIN SRC_DB=$SRC_DB"
```

Contagens de referência (28/07 10:34): **6** veículos, 82 fotos, 5 clientes, 3 vendas, 3 leads, 1 usuário; `storage/app` com **228** arquivos e ~83 MB; **sem** `customer-documents/`.

⚠️ Este tenant **está sendo editado ativamente** — ganhou 19 fotos entre 09:30 e 10:34 de 28/07. Meça a origem na hora e compare com isso, não com o número acima.

**Claude valida:** cada task na mesma ordem. O Task 1.5 (chave SSH) já está feito e não repete.

---

# FASE 2 — Cutover

Só após 48h do Task 0.2 e com o go/no-go do Task 1.11 aprovado. Faça **um domínio por vez**.

### Task 2.1: Congelar a escrita na origem

**Contexto:** o passo que garante que não haja divergência de dados. O site público continua servindo; só o `/admin` fica bloqueado.

- [ ] **Passo 1: 🟧 `[SHARED]` Ler e guardar o `.htaccess` atual**

```bash
cd ~/$DOMAIN
cp .htaccess ~/pre-migracao/htaccess-$DOMAIN.bak 2>/dev/null || echo "(não existe .htaccess na raiz)"
cat .htaccess 2>/dev/null
```

**Esperado:** o conteúdo atual (regras do cPanel / redirect para `public/`).
**Claude valida:** **preciso ver isso antes de escrever a regra** — a posição do bloqueio depende das regras existentes. Cole a saída.

- [ ] **Passo 2: 🟧 `[SHARED]` Inserir o bloqueio do `/admin`**

> Comando exato definido pelo Claude após o Passo 1. A regra a inserir **antes** das regras de rewrite existentes é:
> ```apache
> RewriteEngine On
> RewriteRule ^admin(/.*)?$ - [F,L]
> ```

- [ ] **Passo 3: ⬜ `[MAC]` Confirmar que o público vive e o admin morreu**

```bash
curl -so /dev/null -w 'publico: %{http_code}\n'  --resolve $DOMAIN:443:108.167.132.218 https://$DOMAIN/
curl -so /dev/null -w 'estoque: %{http_code}\n'  --resolve $DOMAIN:443:108.167.132.218 https://$DOMAIN/estoque
curl -so /dev/null -w 'admin:   %{http_code}\n'  --resolve $DOMAIN:443:108.167.132.218 https://$DOMAIN/admin
```

**Esperado:** `publico: 200`, `estoque: 200`, `admin: 403`.
**Claude valida:** exatamente esse padrão. Se o público quebrou, reverta o `.htaccess` imediatamente com o backup do Passo 1.

> ⏱️ **A janela começa agora.** Alvo: 30 minutos até a troca de DNS.

---

### Task 2.2: Sincronização final

- [ ] **Passo 1: 🟦 `[VPS]` Dump final e restauração**

```bash
ssh $SSH_OPTS $REMOTE "~/dump-tenant.sh $DOMAIN" > /root/migracao/$SITE-final.sql
tail -2 /root/migracao/$SITE-final.sql
mysql -e "DROP DATABASE \`$SITE\`; CREATE DATABASE \`$SITE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql $SITE < /root/migracao/$SITE-final.sql
mysql $SITE -e "SELECT 'vehicles' t,COUNT(*) n FROM vehicles UNION ALL SELECT 'photos',COUNT(*) FROM vehicle_photos UNION ALL SELECT 'customers',COUNT(*) FROM customers UNION ALL SELECT 'sales',COUNT(*) FROM sales UNION ALL SELECT 'leads',COUNT(*) FROM leads;"
mysql -e "SELECT DISTINCT table_collation FROM information_schema.tables WHERE table_schema='$SITE';"
```

> Sem `ALTER TABLE CONVERT` aqui: as tabelas chegam do dump já em
> `utf8mb4_unicode_ci` (Task 1.6 Passo 3). O `DROP DATABASE` recria o banco com
> esse mesmo default, então o resultado é idêntico ao da Fase 1.

**Esperado:** `Dump completed`, contagens ≥ as do Task 1.6 (podem ter crescido), e `utf8mb4_unicode_ci`.
**Claude valida:** o `DROP` é seguro porque o admin da origem está bloqueado — nada foi escrito no VPS ainda. Contagem menor que antes = investigar.

- [ ] **Passo 2: 🟦 `[VPS]` Rsync final**

```bash
rsync -az --delete --info=stats2 -e "ssh -i /root/.ssh/id_migracao -p $SSH_PORT" \
  helpdi71@108.167.132.218:~/$DOMAIN/storage/app/ /var/www/$SITE/storage/app/
find /var/www/$SITE/storage/app -type f | wc -l
```

**Esperado:** poucos arquivos transferidos (incremental) e contagem final ≥ a do Task 1.7.
**Claude valida:** o `--delete` alinha exclusões feitas na origem desde o rsync inicial.

- [ ] **Passo 3: 🟦 `[VPS]` Permissões e caches**

```bash
cd /var/www/$SITE
chown -R deploy:www-data storage
chmod -R 775 storage
php8.3 artisan config:clear && php8.3 artisan cache:clear && php8.3 artisan view:clear && php8.3 artisan route:clear
php8.3 artisan config:cache && php8.3 artisan route:cache && php8.3 artisan view:cache
systemctl reload php8.3-fpm
```

**Esperado:** sem erro.
**Claude valida:** `cache:clear` é obrigatório — o `Setting::get` usa `rememberForever`, e valores do dump antigo ficariam grudados.

- [ ] **Passo 4: ⬜ `[MAC]` Fumaça pré-DNS**

```bash
for P in / /estoque /sitemap.xml /robots.txt; do
  printf "%-14s %s\n" "$P" "$(curl -so /dev/null -w '%{http_code}' --resolve $DOMAIN:443:129.121.50.200 https://$DOMAIN$P)"
done
curl -s --resolve $DOMAIN:443:129.121.50.200 https://$DOMAIN/estoque | grep -c 'estoque\|veiculo'
```

**Esperado:** todos 200 e conteúdo presente.
**Claude valida:** última confirmação antes do ponto de não-retorno fácil.

---

### Task 2.3: Trocar o DNS

- [ ] **Passo 1: 🌐 `[cPanel]` Alterar o registro A do apex**

Zone Editor → `$DOMAIN` → Manage → registro **A** do apex → trocar `108.167.132.218` por **`129.121.50.200`**. Salvar.

**Não toque** em `www` (é CNAME e acompanha), nem em `mail`, `webmail`, `cpanel`, `ftp`, `autodiscover`, MX, TXT, DKIM.

- [ ] **Passo 2: ⬜ `[MAC]` Confirmar no NS autoritativo**

```bash
dig +noall +answer A $DOMAIN @ns876.hostgator.com.br
dig +noall +answer A www.$DOMAIN @ns876.hostgator.com.br
```

**Esperado:** apex com `129.121.50.200`; `www` como CNAME resolvendo para o mesmo IP.
**Claude valida:** se o `www` ainda mostrar o IP antigo como registro A próprio (não CNAME), há um segundo registro a trocar.

- [ ] **Passo 3: ⬜ `[MAC]` Acompanhar a propagação**

```bash
for R in 8.8.8.8 1.1.1.1 9.9.9.9 208.67.222.222; do
  printf "%-18s %s\n" "$R" "$(dig +short A $DOMAIN @$R | tr '\n' ' ')"
done
```

**Esperado:** convergindo para `129.121.50.200` em ~5–10 min (TTL 300). Repita até todos mudarem.
**Claude valida:** durante a transição, resolvers divergentes são normais e inofensivos — o conteúdo público é idêntico nos dois servidores e a escrita está bloqueada na origem.

- [ ] **Passo 4: ⬜ `[MAC]` Validar sem `/etc/hosts` nem `--resolve`**

Remova a linha do `/etc/hosts` adicionada no Task 1.11 **antes** deste passo:

```bash
sudo sed -i '' "/^129\.121\.50\.200 .*$DOMAIN/d" /etc/hosts
dscacheutil -flushcache; sudo killall -HUP mDNSResponder
grep -c "129.121.50.200" /etc/hosts
curl -so /dev/null -w 'publico: %{http_code}\n' https://$DOMAIN/
curl -so /dev/null -w 'admin:   %{http_code}\n' https://$DOMAIN/admin
echo | openssl s_client -servername $DOMAIN -connect $DOMAIN:443 2>/dev/null | openssl x509 -noout -subject -dates
```

**Esperado:** `grep -c` retorna `0`; público 200; **admin 200 ou 302** (agora vem do VPS, sem o bloqueio); certificado válido.
**Claude valida:** admin respondendo prova que o tráfego chega ao VPS, não à origem bloqueada.

> 🚦 **Último ponto de rollback fácil.** Reverter agora = trocar o A de volta. Depois da primeira escrita no admin do VPS, voltar exige exportar do VPS para a origem.

- [ ] **Passo 5: ⬜ `[MAC]` Liberar o admin e testar escrita real**

Faça login em `https://$DOMAIN/admin` e edite algo pequeno e reversível (a descrição de um veículo). Confirme que salvou e apareceu no site público.

**Claude valida:** a partir daqui o VPS é a fonte de verdade.

---

### Task 2.4: Repetir para o Friedrich

- [ ] **Passo 1:** refazer os Tasks 2.1 a 2.3 com as variáveis do Friedrich.

**Claude valida:** cada etapa. Só comece depois do Soavel estar estável.

---

# FASE 3 — Consolidar

### Task 3.1: Certificado com renovação automática

**Contexto:** o certificado atual foi emitido por DNS-01 manual e **não renova sozinho**. Agora que o DNS aponta para o VPS, HTTP-01 funciona e entra no automatismo já existente.

- [ ] **Passo 1: 🟦 `[VPS]` Reemitir por HTTP-01**

```bash
certbot --nginx --cert-name $DOMAIN -d $DOMAIN -d www.$DOMAIN --force-renewal --agree-tos --no-eff-email
grep -E 'authenticator|installer' /etc/letsencrypt/renewal/$DOMAIN.conf
```

**Esperado:** `authenticator = nginx`. Se ainda constar `manual`, a renovação automática não funcionará.
**Claude valida:** o `authenticator` é o que importa aqui.

- [ ] **Passo 2: 🟦 `[VPS]` Simular a renovação**

```bash
certbot renew --dry-run 2>&1 | tail -20
```

**Esperado:** `Congratulations, all simulated renewals succeeded` incluindo os dois domínios novos.
**Claude valida:** que os 7 certificados antigos também passaram — o dry-run testa todos.

- [ ] **Passo 3: 🌐 `[cPanel]` Limpar os TXT do desafio**

Zone Editor → remover os registros `_acme-challenge` criados no Task 1.10. Não são mais necessários.

---

### Task 3.2: Scripts de deploy

- [ ] **Passo 1: 🟦 `[VPS]` Ver o padrão existente**

```bash
cat /home/deploy/deploy-masterveiculos.sh
echo "=== rocanossa ==="; cat /home/deploy/deploy-rocanossa.sh
```

**Esperado:** o padrão da casa.
**Claude valida:** escrevo os novos scripts espelhando este formato, não um inventado.

- [ ] **Passo 2: 🟦 `[VPS]` Criar o script**

```bash
cat > /home/deploy/deploy-$SITE.sh <<'EOF'
#!/bin/bash
set -euo pipefail

SITE=__SITE__
APP=/var/www/$SITE
LOCK=/tmp/deploy-$SITE.lock

exec 9>"$LOCK"
flock -n 9 || { echo "Deploy de $SITE já em andamento. Abortando."; exit 1; }

echo "==> Deploy de $SITE — $(date '+%F %T')"
cd "$APP"

# safe.directory idempotente: os scripts existentes fazem --add a cada deploy,
# e por isso o gitconfig global já tem taketicket repetido 15 vezes.
git config --global --get-all safe.directory | grep -qx "$APP" \
  || git config --global --add safe.directory "$APP"

echo "--> modo de manutencao"
php8.3 artisan down --retry=15 || true
trap 'cd "$APP" && php8.3 artisan up || true' EXIT

echo "--> codigo (fetch + reset, padrao masterveiculos/taketicket)"
git fetch origin main
git reset --hard origin/main
git log --oneline -1

echo "--> composer (PHP 8.3 explicito — o php default do servidor e 8.4)"
php8.3 /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction

echo "--> migrations"
php8.3 artisan migrate --force

echo "--> caches"
umask 022   # sem isto os caches nascem 600 e o www-data nao os le -> HTTP 500
php8.3 artisan config:clear && php8.3 artisan cache:clear && php8.3 artisan view:clear && php8.3 artisan route:clear
php8.3 artisan config:cache && php8.3 artisan route:cache && php8.3 artisan view:cache && php8.3 artisan event:cache

echo "--> permissoes (DEPOIS de gerar os caches — a ordem importa)"
chown -R deploy:www-data "$APP"
find "$APP/storage" "$APP/bootstrap/cache" -type d -exec chmod 2775 {} \;
find "$APP/storage" "$APP/bootstrap/cache" -type f -exec chmod 664 {} \;

ILEGIVEIS=$(find "$APP/storage" "$APP/bootstrap/cache" -type f ! -perm -g=r | wc -l)
[ "$ILEGIVEIS" -eq 0 ] || { echo "FALHOU: $ILEGIVEIS arquivos ilegiveis pelo grupo www-data"; exit 1; }

echo "--> reload fpm"
systemctl reload php8.3-fpm

echo "--> saindo do modo de manutencao"
php8.3 artisan up
trap - EXIT

echo "--> smoke test"
CODE=$(curl -so /dev/null -w '%{http_code}' --max-time 20 "https://__DOMAIN__/")
echo "HTTP $CODE"
[ "$CODE" = "200" ] || { echo "FALHOU: home retornou $CODE"; exit 1; }

echo "==> Deploy de $SITE concluido"
EOF
sed -i "s|__SITE__|$SITE|g; s|__DOMAIN__|$DOMAIN|g" /home/deploy/deploy-$SITE.sh
chmod +x /home/deploy/deploy-$SITE.sh
bash -n /home/deploy/deploy-$SITE.sh && echo "SINTAXE OK"
```

**Esperado:** `SINTAXE OK`.
**Claude valida:** quatro coisas que os scripts existentes não têm e que valem a pena:

| Adição | Por quê |
|---|---|
| `flock` | Impede dois deploys simultâneos corrompendo `vendor/` no meio do `composer install` |
| `trap ... EXIT` no `artisan up` | Se o deploy falhar no meio, o site **sai** do modo de manutenção em vez de ficar preso nele. O `deploy-rocanossa.sh` atual deixaria o site fora do ar se o `composer install` falhasse |
| smoke test com `exit 1` | Faz o script **falhar alto** se a home quebrar, em vez de terminar com "Deploy complete" sobre um site 500 |
| `safe.directory` com `--get` antes | Evita o acúmulo infinito no gitconfig global |

> ⚠️ O script precisa rodar **como root** (faz `chown` e `systemctl reload`), igual aos `deploy-*.sh` já existentes, que são `root:root`.

- [ ] **Passo 3: 🟦 `[VPS]` Executar de verdade**

```bash
/home/deploy/deploy-$SITE.sh
```

**Esperado:** todas as etapas e `HTTP 200`. O `git pull` dirá `Already up to date`.
**Claude valida:** critério de conclusão nº 5 do spec.

---

### Task 3.3: Backup

**Contexto:** o `backup.sh` atual já cobre os bancos novos (usa `--all-databases`), mas o tar cobre **apenas** o treinaedu. Os `storage/app` dos outros sites — incluindo `customer-documents/` e `vehicle-documents/` — **não têm backup nenhum hoje**.

- [ ] **Passo 1: 🟦 `[VPS]` Confirmar o problema**

```bash
cat /home/deploy/backup.sh
```

**Esperado:** o tar apontando só para `/var/www/treinaedu/storage/app/public`.
**Claude valida:** confirma a lacuna antes de reescrever.

- [ ] **Passo 2: 🟦 `[VPS]` Reescrever cobrindo todos os sites**

```bash
cp /home/deploy/backup.sh /home/deploy/backup.sh.bak-$(date +%F)
cat > /home/deploy/backup.sh <<'EOF'
#!/bin/bash
set -euo pipefail

DATE=$(date +%Y-%m-%d_%H-%M)
BACKUP_DIR="/home/deploy/backups"
DAYS_TO_KEEP=14

mkdir -p "$BACKUP_DIR"

# ── Bancos (todos) ────────────────────────────────────────────
mysqldump -u root --all-databases --single-transaction --no-tablespaces \
  | gzip > "$BACKUP_DIR/mysql_$DATE.sql.gz"

# ── storage/app de cada site (inclui documentos privados) ─────
for APP in /var/www/*/; do
  NAME=$(basename "$APP")
  [ -d "$APP/storage/app" ] || continue
  tar czf "$BACKUP_DIR/storage-${NAME}_$DATE.tar.gz" -C "$APP/storage" app
done

# ── Configuração do servidor ──────────────────────────────────
tar czf "$BACKUP_DIR/etc-config_$DATE.tar.gz" \
  /etc/nginx/sites-available /etc/php/*/fpm/pool.d /etc/supervisor/conf.d 2>/dev/null

# ── .env de cada site (contêm APP_KEY — indispensáveis) ───────
tar czf "$BACKUP_DIR/envs_$DATE.tar.gz" /var/www/*/.env 2>/dev/null

find "$BACKUP_DIR" -type f -mtime +$DAYS_TO_KEEP -delete

echo "Backup concluido: $DATE"
du -sh "$BACKUP_DIR"
EOF
chmod +x /home/deploy/backup.sh
bash -n /home/deploy/backup.sh && echo "SINTAXE OK"
```

**Esperado:** `SINTAXE OK`.
**Claude valida:** retenção subiu para 14 dias; os `.env` entram porque perder o `APP_KEY` inviabiliza restaurar dados cifrados.

- [ ] **Passo 3: 🟦 `[VPS]` Rodar e verificar**

```bash
/home/deploy/backup.sh
ls -lh /home/deploy/backups/ | tail -15
tar tzf /home/deploy/backups/storage-${SITE}_*.tar.gz 2>/dev/null | head -5
df -h / | tail -1
```

**Esperado:** um `.tar.gz` por site, o `mysql_*.sql.gz`, e o conteúdo do tar do site listável. Disco ainda com folga.
**Claude valida:** `tar tzf` prova que o arquivo não está corrompido — backup não testado não é backup.

- [ ] **Passo 4: 🟦 `[VPS]` Destino off-site com rclone**

```bash
command -v rclone || (curl -s https://rclone.org/install.sh | bash)
rclone version | head -1
rclone config
```

Configure o destino que preferir (Backblaze B2 é o mais barato; Google Drive serve). Nome do remote: `offsite`.

- [ ] **Passo 5: 🟦 `[VPS]` Ligar o off-site ao backup**

```bash
cat >> /home/deploy/backup.sh <<'EOF'

# ── Replicação off-site ───────────────────────────────────────
if rclone listremotes 2>/dev/null | grep -q '^offsite:'; then
  rclone sync "$BACKUP_DIR" offsite:vps-backups --max-age 15d --transfers 4 2>&1 | tail -5
  echo "Off-site sincronizado"
else
  echo "AVISO: remote 'offsite' nao configurado — backup apenas local" >&2
fi
EOF
bash -n /home/deploy/backup.sh && /home/deploy/backup.sh
rclone ls offsite:vps-backups | tail -5
```

**Esperado:** `Off-site sincronizado` e os arquivos listados no destino remoto.
**Claude valida:** verificamos no destino, não só a ausência de erro. O `if` faz o script avisar em vez de falhar silencioso.

---

### Task 3.4: Observação de 48h

- [ ] **Passo 1: 🟦 `[VPS]` Checagem diária (rodar em D+1 e D+2)**

```bash
echo "=== $(date) ==="
for U in https://soavelveiculos.com.br https://friedrichveiculos.com.br https://helpflux.com.br https://veiculos.helpflux.com.br https://rocanossa.com.br https://taketicket.com.br https://treinaedu.com.br https://meet.treinaedu.com.br https://grafana.helpflux.com.br; do
  printf "%-42s %s\n" "$U" "$(curl -so /dev/null -w '%{http_code}' --max-time 15 $U)"
done
free -m | head -2
swapon --show
grep -ic 'oom-kill' /var/log/syslog || true
for S in soavelveiculos friedrichveiculos; do
  echo "-- $S laravel.log:"; grep -icE 'ERROR|CRITICAL|Exception' /var/www/$S/storage/logs/laravel.log 2>/dev/null || echo 0
  echo "-- $S pool:";        tail -3 /var/log/php8.3-fpm-$S.log 2>/dev/null || echo "(vazio)"
done
```

**Esperado:** todos os 9 sites respondendo; `oom-kill` em `0`; swap sem crescimento relevante; contagem de erro em 0.
**Claude valida:** comparo com a baseline do Task 1.1 e sinalizo qualquer tendência de consumo de memória.

---

# FASE 4 — Escalar e limpar

### Task 4.1: Script de provisionamento

**Contexto:** você quer adicionar tenants. Este script substitui os 11 passos manuais do `docs/novo-tenant.md`.

- [ ] **Passo 1:** Claude escreve `/home/deploy/provision-tenant.sh` a partir dos artefatos **já validados** nas fases 1 e 2 (pool, vhost, criação de banco, permissões), com `set -euo pipefail`, idempotência e verificação por etapa.

- [ ] **Passo 2: 🟦 `[VPS]` Validar criando um tenant descartável**

```bash
/home/deploy/provision-tenant.sh teste-tenant teste.helpflux.com.br
```

**Esperado:** site provisionado e respondendo.
**Claude valida:** critério de conclusão nº 6.

- [ ] **Passo 3: 🟦 `[VPS]` Remover o tenant de teste**

```bash
/home/deploy/provision-tenant.sh --destroy teste-tenant
```

**Claude valida:** que o `--destroy` limpa diretório, banco, usuário, pool e vhost, sem tocar em nada mais.

---

### Task 4.2: Atualizar a documentação

- [ ] **Passo 1: ⬜ `[MAC]` Reescrever `docs/novo-tenant.md` para o fluxo VPS**

Substituir os passos de cPanel pelo `provision-tenant.sh`, mantendo a seção "Cadastrar no Master" (que segue manual, por envolver dados comerciais).

- [ ] **Passo 2: ⬜ `[MAC]` Commitar**

```bash
cd /Users/joaofilipibritto/Projetos/site-carros/soavel
git add docs/novo-tenant.md docs/superpowers/
git commit -m "docs: fluxo de provisionamento de tenant no VPS"
```

---

### Task 4.3: Desativar a origem (T+7 dias)

**Contexto:** só após 7 dias de operação estável. A zona DNS **vive no cPanel** — remover o domínio de lá derruba os dois sites.

- [ ] **Passo 1: 🟧 `[SHARED]` Arquivar em vez de apagar**

```bash
cd ~
tar czf ~/pre-migracao/ARQUIVO-FINAL-soavelveiculos-$(date +%F).tar.gz soavelveiculos.com.br
tar czf ~/pre-migracao/ARQUIVO-FINAL-friedrichveiculos-$(date +%F).tar.gz friedrichveiculos.com.br
ls -lh ~/pre-migracao/ARQUIVO-FINAL-*
```

**Esperado:** dois arquivos.
**Claude valida:** existência antes de qualquer remoção.

- [ ] **Passo 2: ⬜ `[MAC]` Baixar os arquivos para fora do servidor**

```bash
scp -P $SSH_PORT helpdi71@108.167.132.218:~/pre-migracao/ARQUIVO-FINAL-*.tar.gz ~/Downloads/
ls -lh ~/Downloads/ARQUIVO-FINAL-*
```

**Claude valida:** cópia fora dos dois servidores antes de apagar.

- [ ] **Passo 3: 🟧 `[SHARED]` Remover os arquivos dos sites**

```bash
rm -rf ~/soavelveiculos.com.br/vendor ~/soavelveiculos.com.br/storage
rm -rf ~/friedrichveiculos.com.br/vendor ~/friedrichveiculos.com.br/storage
du -sh ~
```

**Esperado:** queda de ~900 MB no uso do home.
**Claude valida:** manter o diretório raiz e o domínio no cPanel — só o conteúdo pesado sai. Os bancos antigos ficam mais tempo como rede de segurança.

> ⛔ **Nunca** remover os domínios do cPanel (Domínios → Remover). A zona DNS deles vive lá.

---

## Rollback

| Momento | Como voltar |
|---|---|
| Durante a Fase 1 | Nada a fazer — produção intocada. Opcionalmente remover `/var/www/$SITE`, banco, pool e vhost. |
| Após Task 2.1 (admin bloqueado) | Restaurar o `.htaccess` do backup. |
| Após Task 2.3 (DNS trocado), antes de escrever no admin novo | Trocar o registro A de volta para `108.167.132.218`. Com TTL 300, volta em minutos. |
| Após escrever no admin do VPS | Exportar do VPS e importar na origem antes de reverter o DNS. Por isso o go/no-go fica no Task 2.3 Passo 4. |

---

## Rastreio de progresso

| Fase | Task | Soavel | Friedrich |
|---|---|---|---|
| 0 | 0.1 cron órfão | ☐ | — |
| 0 | 0.2 TTL 300 | ☐ | ☐ |
| 1 | 1.1 backups | ☐ | — |
| 1 | 1.2 acesso git | ☐ | — |
| 1 | 1.3 código | ☐ | ☐ |
| 1 | 1.4 banco | ☐ | ☐ |
| 1 | 1.5 canal SSH | ☐ | — |
| 1 | 1.6 migrar banco | ☐ | ☐ |
| 1 | 1.7 arquivos | ☐ | ☐ |
| 1 | 1.8 pool FPM | ☐ | ☐ |
| 1 | 1.9 vhost | ☐ | ☐ |
| 1 | 1.10 certificado | ☐ | ☐ |
| 1 | 1.11 **go/no-go** | ☐ | ☐ |
| 2 | 2.1 congelar | ☐ | ☐ |
| 2 | 2.2 sync final | ☐ | ☐ |
| 2 | 2.3 DNS | ☐ | ☐ |
| 3 | 3.1 cert auto | ☐ | ☐ |
| 3 | 3.2 deploy.sh | ☐ | ☐ |
| 3 | 3.3 backup | ☐ | — |
| 3 | 3.4 48h | ☐ | — |
| 4 | 4.1 provision | ☐ | — |
| 4 | 4.2 docs | ☐ | — |
| 4 | 4.3 limpeza | ☐ | ☐ |
