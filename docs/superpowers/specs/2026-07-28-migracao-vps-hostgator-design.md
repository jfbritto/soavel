# Migração HostGator Compartilhado → VPS: soavelveiculos.com.br e friedrichveiculos.com.br

**Data:** 2026-07-28
**Status:** Aprovado, pronto para plano de implementação
**Escopo:** Mover os dois tenants Laravel de veículos da hospedagem compartilhada HostGator (plano M, conta `helpdi71`) para o VPS Ubuntu já existente em `129.121.50.200`, com indisponibilidade próxima de zero.

---

## 1. Situação atual (medida, não presumida)

### 1.1 Origem — compartilhado HostGator

Conta cPanel `helpdi71`, home `/home1/helpdi71`, IP `108.167.132.218` (`unifiedlayer.com`), 4,3 GB usados.
Hospeda **nove** aplicações Laravel. Apenas duas migram:

| | soavelveiculos.com.br | friedrichveiculos.com.br |
|---|---|---|
| Diretório | `/home1/helpdi71/soavelveiculos.com.br` (522 MB) | `/home1/helpdi71/friedrichveiculos.com.br` (393 MB) |
| Banco | `helpdi71_soavelveiculos` (0,7 MB) | `helpdi71_friedrichveiculos` (0,7 MB) |
| Usuário MySQL | `helpdi71_master` (**compartilhado por todos os 9 apps**) | idem |
| `storage/app` | 101 MB reais / 310 arquivos | 83 MB reais / 228 arquivos ⚠️ |
| `storage/app/public/vehicles` | 100 MB | 66 MB |
| `storage/app/public/settings` | 404 KB | 644 KB |
| Privados fora do `public` | `customer-documents/`, `vehicle-documents/` | `vehicle-documents/` |
| Registros | 34 veículos, 293 fotos, 18 clientes, 19 vendas, 3 leads, 1 usuário | 6 veículos, 82 fotos, 5 clientes, 3 vendas, 3 leads, 1 usuário |
| `APP_URL` | `https://soavelveiculos.com.br/` ⚠️ barra final | `https://friedrichveiculos.com.br` |
| Git HEAD | `436c8ea` (main) | `436c8ea` (main) |

- **PHP 8.3.32** (`ea-php83`). Extensões relevantes presentes: `gd`, `exif`, `imagick`, `pdo_mysql`, `zip`, `intl`, `redis`, `apcu`.
- **MySQL 5.7.44-48**, `collation_server = utf8_unicode_ci`.
- Drivers: `CACHE_DRIVER=file`, `SESSION_DRIVER=file`, `QUEUE_CONNECTION=sync`, `FILESYSTEM_DRIVER=local`. `SESSION_LIFETIME=43200` (30 dias).
- `public/storage` é symlink absoluto para `storage/app/public/`.
- `public/img` (14 MB) e `public/images` (300 KB) são **versionados no git** — vêm pelo clone, não precisam de rsync.
- Nenhuma tarefa cron para os dois sites.
- **Nenhuma caixa de e-mail** para os dois domínios (`~/mail` só tem beautymetrics, decasaemcasa, helpcheck, helpdiet).
- `git status` limpo em ambos, exceto arquivos não versionados `.htaccess` e `.htaccess.phpupgrader.*` — artefatos do seletor de PHP do cPanel. **Não devem ir para o VPS.**

### 1.2 Destino — VPS

`vps-15124388`, `129.121.50.200`, Ubuntu 22.04.5 LTS, 2 vCPU, 3,8 GB RAM (1,6 GB disponível), 6 GB de swap, 99 GB de disco com 73 GB livres, `OOM: 0`.

Já em produção: `helpflux.com.br`, `veiculos.helpflux.com.br` (Master), `rocanossa.com.br`, `taketicket.com.br`, `treinaedu.com.br`, `meet.treinaedu.com.br` (Jitsi em 4 contêineres Docker), `grafana.helpflux.com.br`.

- nginx, um vhost por site em `sites-available/` + symlink em `sites-enabled/`, `root` em `<projeto>/public`, `client_max_body_size 20M`.
- **PHP 8.3 e 8.4** via PPA `ondrej` (jammy). Pool único `[www]` por versão, rodando como `www-data`. `php8.3` com `pm.max_children = 5`; `php8.4` com `pm.max_children = 20`.
- **MySQL 8.0.46**, `collation_server = utf8mb4_0900_ai_ci`, `innodb_buffer_pool_size = 128M`. Bancos existentes somam ~6 MB.
- Usuário `deploy` (home `/home/deploy`); apps em `/var/www/<projeto>`, dono `deploy` com grupo `deploy` ou `www-data` (inconsistente entre sites).
- `certbot` com 7 certificados e renovação automática via `/etc/cron.d/certbot` (`0 */12 * * *`).
- `supervisor` para workers de fila (rocanossa, taketicket, treinaedu).
- Scripts de deploy por projeto em `/home/deploy/deploy-<projeto>.sh`.
- `/home/deploy/backup.sh` diário às 3h: `mysqldump --all-databases` + tar **apenas** do `storage/app/public` do treinaedu, retenção 7 dias, tudo local.
- `ufw` ativo: OpenSSH, `22022/tcp`, 80, 443.
- Redis instalado (o worker do treinaedu usa `queue:work redis`).

### 1.3 DNS

Ambos os domínios delegam para `ns876.hostgator.com.br` / `ns877.hostgator.com.br` — **a zona é editada no cPanel da HostGator; o Registro.br guarda apenas a delegação.**

| Registro | Valor atual | Ação no cutover |
|---|---|---|
| `@` A | `108.167.132.218`, TTL 14400 | **trocar para `129.121.50.200`** |
| `www` CNAME | → apex | nada (segue o apex automaticamente) |
| `mail`, `webmail`, `cpanel`, `ftp`, `autodiscover` A | `108.167.132.218` | **preservar** |
| MX | `0 mail.<domínio>` | preservar |
| SPF | `v=spf1 a mx include:websitewelcome.com ~all` | preservar |
| DKIM (`default._domainkey`) | presente | preservar |
| DMARC | ausente | fora de escopo |

Não existe wildcard DNS — cada subdomínio é registro explícito, então mover o apex não arrasta os registros de e-mail.

---

## 2. Achado urgente, fora do escopo desta migração

`veiculos.helpflux.com.br` (o Master) **já foi migrado** para o VPS. A cópia antiga em `/home1/helpdi71/veiculos.helpflux.com.br` continua no compartilhado com cron ativo:

```
*/17 * * * * cd ~/veiculos.helpflux.com.br && /opt/cpanel/ea-php83/root/usr/bin/php artisan schedule:run
```

Roda contra `helpdi71_masterveiculos` (0,4 MB) enquanto o banco vivo é `masterveiculos` no VPS (1,6 MB). Como o Master integra com o Asaas para cobrança, um `schedule:run` a cada 17 minutos sobre estado obsoleto pode produzir cobrança ou notificação duplicada.

**Ação recomendada, imediata e independente:** comentar essa linha do crontab do compartilhado. Também vale revisar `*/24` e `*/22` de `beautymetrics.com.br`, que continua legitimamente hospedado lá.

---

## 3. Decisões de arquitetura

### 3.1 Stack: bare metal seguindo a convenção existente

O VPS já tem sete sites em nginx + PHP-FPM + MySQL nativo, com padrão de diretórios, deploy e SSL estabelecido. A migração **adota essa convenção** em vez de introduzir Docker ou painel de controle. Duas migrações anteriores (`helpflux`, `masterveiculos`) já validaram o caminho.

### 3.2 PHP 8.3, sem instalar versão nova

O compartilhado já roda o app em **PHP 8.3.32 em produção**. O `composer.json` declara `^7.3|^8.0`, compatível. Usamos o `php8.3-fpm` que já existe. Não instalamos PHP 8.1 (que está EOL e sem correções de segurança).

`intervention/image` 2.x usa o driver **GD** por padrão (não há `config/image.php` no projeto), e `gd` está presente no PHP 8.3 do VPS. O `imagick` disponível no compartilhado não é usado.

### 3.3 Pool FPM dedicado por site — resolve o bloqueador de upload

O VPS tem `upload_max_filesize = 2M` e `post_max_size = 8M` nos dois `php.ini` de FPM, **sem nenhum override**. Os vhosts só ajustam `client_max_body_size 20M` no nginx, que não levanta o teto do PHP. Fotos de celular têm 3–8 MB: o upload de veículo falharia no primeiro uso.

Cada site recebe um pool próprio (`/etc/php/8.3/fpm/pool.d/<site>.conf`) com socket e limites próprios, **sem tocar no pool compartilhado dos outros sites**:

```ini
[soavelveiculos]
user = www-data
group = www-data
listen = /run/php/php8.3-fpm-soavelveiculos.sock
listen.owner = www-data
listen.group = www-data

pm = ondemand
pm.max_children = 4
pm.process_idle_timeout = 30s
pm.max_requests = 500

php_admin_value[upload_max_filesize] = 20M
php_admin_value[post_max_size] = 24M
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 120
php_admin_value[error_log] = /var/log/php8.3-fpm-soavelveiculos.log
php_admin_flag[log_errors] = on
```

`pm = ondemand` é escolha deliberada: com 1,6 GB livres e o Jitsi na máquina, dois sites institucionais de baixo tráfego não devem manter processos ociosos. `memory_limit = 256M` cobre o redimensionamento de JPEG pelo GD.

**Observação registrada, não tratada aqui:** o pool `php8.4` está com `pm.max_children = 20` e processos observados em ~100 MB, ou seja, ~2 GB de comprometimento teórico contra 3,8 GB de RAM. Está sobrecomprometido hoje. Não mexemos nele nesta migração para não afetar sites em produção; vale medir e ajustar depois.

### 3.4 MySQL: nenhuma conversão de charset é necessária

> **Corrigido em 28/07 após medição.** A versão original desta seção previa
> converter as tabelas de `utf8mb3` para `utf8mb4`, partindo do
> `collation_server = utf8_unicode_ci` da origem. Isso estava errado: o
> `collation_server` é o default do servidor compartilhado, não o collation das
> tabelas. As tabelas foram criadas pelas migrations do Laravel, e
> `config/database.php` declara `utf8mb4` / `utf8mb4_unicode_ci`
> explicitamente — então sempre foram utf8mb4.

Verificado no VPS após a restauração do dump do Soavel:

```
SELECT DISTINCT table_collation FROM information_schema.tables WHERE table_schema='soavelveiculos';
-> utf8mb4_unicode_ci   (linha única, todas as 18 tabelas)
```

Origem `5.7.44-48`, destino `8.0.46`. O banco de destino é criado como
`utf8mb4` / `utf8mb4_unicode_ci`, alinhado com o `config/database.php`, e as
tabelas chegam já nesse collation pelo próprio dump. **Nenhum `ALTER TABLE
CONVERT` é executado**, o que elimina de uma vez o risco de índice acima do
limite de prefixo.

Permanece a verificação de integridade de acentuação após a restauração (§6):
ela testa se os dados sobreviveram, independentemente do raciocínio sobre
charset.

### 3.5 Usuário MySQL por site

Hoje os nove apps do compartilhado usam o mesmo `helpdi71_master`, o que significa que qualquer app comprometido lê o banco de todos. No VPS cada site recebe usuário próprio com privilégios restritos ao seu banco. É correção de segurança de custo zero no momento da criação.

### 3.6 Permissões

Dono `deploy:www-data`, com `storage/` e `bootstrap/cache` group-writable e setgid — padrão que `helpflux` e `rocanossa` já usam no VPS (mais correto que o `deploy:deploy` de `masterveiculos`/`taketicket`/`treinaedu`, já que o FPM roda como `www-data`).

### 3.7 Filas e agendamento

`QUEUE_CONNECTION=sync` e `app/Console/Kernel.php` sem nenhum `$schedule->` — **nenhum worker do supervisor e nenhum cron são necessários** para estes dois sites.

---

## 4. Estratégia de cutover

Objetivo aprovado: site público nunca sai do ar; apenas o `/admin` fica bloqueado por ~30 minutos.

### 4.1 T-48h — preparar o DNS

Reduzir o TTL do registro A do apex de 14400 para **300** nos dois domínios, via cPanel → Zone Editor. A janela de 48h garante que o valor antigo de 4h expire em todos os resolvers antes da virada.

### 4.2 T-24h — provisionar e validar no VPS

Tudo montado e testado **antes** de qualquer mudança de DNS:

1. Criar diretório, clonar repositório, `composer install --no-dev --optimize-autoloader`.
2. Criar banco e usuário; restaurar um dump inicial.
3. `rsync` inicial do `storage/app`.
4. Copiar o `.env` do compartilhado **preservando o `APP_KEY` original** — trocá-lo invalidaria sessões e qualquer coluna criptografada.
5. Criar o pool FPM e o vhost nginx.
6. Emitir certificado por **desafio DNS-01**, para o HTTPS já estar pronto no instante da virada.
7. Validar por `curl --resolve` apontando o domínio real para o IP do VPS, sem tocar no DNS público.

### 4.3 Virada (~30 min)

1. **Bloquear apenas o `/admin` no compartilhado**, mantendo o site público servindo normalmente. Regra no `.htaccess` da raiz do app (arquivo não versionado, gerado pelo cPanel — ler o conteúdo atual antes de editar e guardar cópia):
   ```apache
   RewriteEngine On
   RewriteRule ^admin(/.*)?$ - [F,L]
   ```
   Isso congela toda escrita sem derrubar o público — é o que garante que não haja divergência de dados durante a propagação.
2. `mysqldump` final → restaurar no VPS → converter charset.
3. `rsync` final do `storage/app` (incremental, poucos segundos).
4. Limpar e regerar caches no VPS; recriar o symlink `public/storage`.
5. Rodar o checklist de fumaça (§6) contra o VPS via `curl --resolve`.
6. **Trocar o registro A do apex** de `108.167.132.218` para `129.121.50.200` nos dois domínios. O `www` é CNAME para o apex e acompanha sozinho.
7. Acompanhar a propagação (~5–10 min com TTL 300).

Durante a propagação, parte dos visitantes ainda cai no servidor antigo. Como o conteúdo público é idêntico e a escrita está bloqueada lá, não há divergência nem erro visível. É isso que produz o "quase zero" real.

### 4.4 Pós-virada

1. Reemitir os certificados por **HTTP-01** (`certbot --nginx`), para a renovação entrar no automatismo já existente em `/etc/cron.d/certbot`.
2. Estender `/home/deploy/backup.sh`: os novos bancos já entram sozinhos porque o script usa `--all-databases`, mas o tar cobre **apenas** o treinaedu — precisa passar a cobrir `storage/app` de todos os sites (inclusive os privados `customer-documents/` e `vehicle-documents/`, que hoje não têm backup nenhum).
3. Adicionar destino **off-site** via `rclone` (decisão aprovada: local + off-site).
4. Criar `/home/deploy/deploy-soavelveiculos.sh` e `deploy-friedrichveiculos.sh` seguindo o padrão existente.
5. Manter o `/admin` bloqueado no compartilhado **permanentemente**, para eliminar risco de escrita numa cópia órfã.
6. Monitorar `storage/logs/laravel.log` e o log do pool FPM por 48h.

### 4.5 T+7d — desativar a origem

Remover os arquivos das duas cópias antigas, **sem remover os domínios do cPanel**: a zona DNS vive lá, e apagar o domínio derruba o DNS dos dois sites. Os bancos antigos ficam mais tempo, como rede de segurança.

### 4.6 Rollback

Reverter o registro A para `108.167.132.218` e remover o bloqueio do `/admin`. Com TTL 300, o retorno é de minutos. O rollback é seguro em qualquer ponto **até** a primeira escrita no admin do VPS — depois disso, voltar exige exportar do VPS para o compartilhado, então o go/no-go fica antes de liberar o admin novo.

---

## 5. Correções de carona

Pequenas, de baixo risco, e o momento é oportuno:

| Item | Problema | Correção |
|---|---|---|
| `APP_URL` do soavel | `https://soavelveiculos.com.br/` com barra final produz `//storage` nas URLs de imagem (o disco `public` usa `env('APP_URL').'/storage'`) | remover a barra |
| Reset de senha | `MAIL_HOST=mailhog` / `MAIL_PORT=1025` nos dois `.env`: a rota `password.email` do Breeze lança exceção de conexão em produção | `MAIL_MAILER=log` no mínimo, ou SMTP real |
| `APP_DEBUG` | conferir `false` nos dois (está correto hoje; validar após copiar o `.env`) | manter `false` |
| Usuário MySQL | compartilhado entre 9 apps | um por site (§3.5) |
| Backup dos privados | `customer-documents/` e `vehicle-documents/` sem backup | incluir no `backup.sh` |

Não são causadas pela migração — são pré-existentes e ficam melhor resolvidas aqui do que depois.

---

## 6. Verificação

Checklist executado em T-24h contra o hostname temporário e repetido na virada, para cada domínio:

**Site público**
- [ ] Home carrega com logo e favicon do tenant correto (vêm de `storage/app/public/settings`)
- [ ] `/estoque` lista os veículos com `status='disponivel'` (16 no Soavel, dos 34 no banco — o site filtra por `Vehicle::disponivel()`)
- [ ] Página de veículo abre pelo slug, com galeria completa e thumbs
- [ ] Imagens servidas por `/storage/vehicles/...` retornam 200 (valida o symlink)
- [ ] `/sitemap.xml` e `/robots.txt` respondem
- [ ] `POST /contato` e `POST /interesse/{vehicle}` gravam lead

**Admin**
- [ ] Login funciona com o usuário existente
- [ ] Listagens de veículos, clientes, vendas, leads, despesas e parceiros com as contagens da §1.1
- [ ] **Upload de foto de veículo com arquivo de ~6 MB** (valida o pool FPM — é o teste que expõe o bloqueador de 2M)
- [ ] Redimensionamento e thumb gerados pelo GD
- [ ] Exclusão de foto remove arquivo e thumb do disco
- [ ] Upload e download de documento de cliente (valida `storage/app` privado, fora do `public`)
- [ ] Configurações salvam e refletem no site (valida `Setting::set` + invalidação de cache)

**Infraestrutura**
- [ ] HTTPS válido, cadeia completa, redirect 80 → 443
- [ ] `nginx -t` sem erro e **os sete sites pré-existentes continuam respondendo** (regressão de coabitação)
- [ ] Master alcança `https://<domínio>/api/master/*` com o `MASTER_API_TOKEN` — o Master está no mesmo servidor, então o caminho passa a ser público-para-si-mesmo
- [ ] `CheckSuspended` não bloqueia indevidamente
- [ ] Contagem de arquivos em `storage/app` bate com a origem **medida no momento do rsync**
- [ ] Charset das tabelas conforme decidido em §3.4
- [ ] Sem `OOM` e sem erro no log do pool após 24h
- [ ] `backup.sh` roda e o arquivo off-site chega

---

## 7. Provisionamento de novos tenants

Objetivo declarado é escalar clientes. O plano entrega `/home/deploy/provision-tenant.sh`, que automatiza o que hoje são 11 passos manuais em [docs/novo-tenant.md](../../novo-tenant.md):

Recebe domínio e nome do tenant, e executa: criar diretório e clonar repo → criar banco e usuário com senha aleatória → gerar `.env` a partir de template com `APP_KEY` nova → `composer install` → `migrate --seed` → `storage:link` → gerar pool FPM a partir de template → gerar vhost nginx a partir de template → `nginx -t` e reload → `certbot --nginx` → imprimir o checklist do cadastro no Master (que continua manual, por envolver dados comerciais).

Idempotente e abortando em qualquer falha, para poder ser reexecutado com segurança. O `docs/novo-tenant.md` é reescrito para o fluxo VPS.

---

## 8. Fora de escopo

Registrado como dívida real, deliberadamente não tratado aqui:

- **Laravel 8 → 11** e **laravel-mix → Vite.** Laravel 8 está EOL (sem correções de segurança desde jan/2023). Acoplar upgrade de framework a migração de infraestrutura multiplica as variáveis de falha; cada um deve ser verificável em isolamento.
- **Mover o DNS para fora da HostGator.** A zona depende do plano M continuar ativo. Como o helpdiet e outros seis apps permanecem lá, a dependência é aceitável por ora. Migrar a delegação para o Registro.br ou Cloudflare desacopla — vale como fase 2.
- **Ajustar `pm.max_children` do pool php8.4** (§3.3).
- **DMARC** nos dois domínios.
- **Isolamento por usuário de sistema** entre pools FPM: todos rodam como `www-data`, seguindo a convenção atual do servidor.
- **Migração dos outros sete apps** do compartilhado.

---

## 9. Riscos

| Risco | Probabilidade | Impacto | Mitigação |
|---|---|---|---|
| Upload falha por limite de 2M do PHP | **Alta** se não tratado | Admin inutilizável | Pool dedicado (§3.3) + teste com arquivo de 6 MB no checklist |
| ~~Conversão de charset 5.7 → 8.0 falha~~ | — | — | **Eliminado em 28/07:** as tabelas já são `utf8mb4_unicode_ci` na origem; nenhuma conversão é executada (§3.4) |
| Escrita no admin antigo durante a propagação | Média | Perda de dados | Bloqueio do `/admin` **antes** do dump final |
| Mudança de config compartilhada quebra os 7 sites | Baixa | Incidente amplo | Nenhuma alteração em pool ou `php.ini` compartilhados; `nginx -t` e regressão no checklist |
| RAM insuficiente com dois sites a mais | Baixa | Swap / OOM | `pm = ondemand` com teto de 4; 6 GB de swap; monitoramento 48h |
| Certificado indisponível na virada | Baixa | Erro de HTTPS | Emissão por DNS-01 **antes** do cutover |
| `.htaccess` do cPanel copiado para o VPS | Baixa | Confusão | Excluído explicitamente; nginx não os lê |
| Perder a zona DNS ao limpar o compartilhado | Baixa | Sites fora do ar | Domínios permanecem no cPanel (§4.5) |
| `APP_KEY` trocada | Baixa | Sessões e dados cifrados perdidos | Copiar o `.env` preservando a chave |
| Master e tenants no mesmo servidor | — | Destino compartilhado | Aceito; registrado como característica da topologia |

---

## 10. Critérios de conclusão

1. Os dois domínios servem do VPS com HTTPS válido e renovação automática.
2. Todo o checklist da §6 passa, incluindo upload de 6 MB.
3. Os sete sites pré-existentes continuam funcionando.
4. Backup diário cobre os dois bancos e todo o `storage/app`, com cópia off-site verificada.
5. `deploy-soavelveiculos.sh` e `deploy-friedrichveiculos.sh` executados com sucesso ao menos uma vez.
6. `provision-tenant.sh` validado criando um tenant descartável.
7. Cron órfão do Master desativado no compartilhado.
8. `docs/novo-tenant.md` reescrito para o fluxo VPS.
9. Cópias antigas desativadas, com domínios preservados no cPanel para o DNS.
