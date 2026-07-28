# Novo Tenant — Passo a Passo

Guia para provisionar um novo cliente no sistema HelpFlux Veiculos.

> **Atualizado em 28/07/2026 para o VPS `129.121.50.200`.** Os sites saíram da
> hospedagem compartilhada HostGator, então o fluxo antigo de cPanel não se
> aplica mais. Contexto completo em
> [docs/superpowers/specs/2026-07-28-migracao-vps-hostgator-design.md](superpowers/specs/2026-07-28-migracao-vps-hostgator-design.md).

---

## Resumo

O que antes eram 11 passos manuais no cPanel virou um script:

```bash
ssh root@129.121.50.200
/home/deploy/provision-tenant.sh <site> <dominio>
```

Ele cria diretório, clone, dependências, banco com usuário isolado, `.env` com
`APP_KEY` nova, schema, permissões, pool PHP-FPM dedicado, vhost nginx,
certificado SSL com renovação automática e o wrapper de deploy. Aborta em
qualquer falha e verifica o resultado no fim.

Fonte do script: [`scripts/vps/provision-tenant.sh`](../scripts/vps/provision-tenant.sh)

---

## 1. Apontar o DNS primeiro

O certificado é emitido por validação HTTP-01, que exige o domínio já resolvendo
para o VPS. **Faça isso antes de rodar o script.**

No painel de DNS do domínio, aponte o registro `A` do apex para:

```
129.121.50.200
```

O `www` normalmente é `CNAME` do apex e acompanha sozinho. Confirme:

```bash
dig +short A <dominio> @8.8.8.8      # deve retornar 129.121.50.200
```

Se o domínio está vindo de outro servidor, reduza o TTL para `300` **antes** e
espere o TTL antigo expirar. Para saber se já expirou, consulte vários resolvers
e veja se todos já servem o TTL novo — mais confiável que contar horas:

```bash
for R in 8.8.8.8 1.1.1.1 9.9.9.9; do dig +noall +answer A <dominio> @$R; done
```

Rodar o script antes do DNS apontar não quebra nada: ele provisiona em HTTP,
avisa, e mostra o comando para emitir o certificado depois.

## 2. Provisionar

```bash
ssh root@129.121.50.200
/home/deploy/provision-tenant.sh friedrichveiculos friedrichveiculos.com.br
```

Primeiro argumento: nome do diretório e do banco. Segundo: o domínio.
Normalmente coincidem, mas podem diferir.

Para usar outro repositório: `REPO=https://github.com/... provision-tenant.sh ...`

## 3. Cadastrar no Master

Em `veiculos.helpflux.com.br/tenants` → **Novo Tenant**:

- **Nome:** nome comercial do cliente
- **Domínio:** `https://<dominio>`
- **Mensalidade:** valor combinado
- **Dados do proprietário:** nome, CPF/CNPJ, email, telefone

O **API Token** é gerado automaticamente. Copie para o `.env` do tenant:

```bash
cd /var/www/<site>
sed -i 's|^MASTER_API_TOKEN=.*|MASTER_API_TOKEN=<token>|' .env
php8.3 artisan config:cache
```

> Sem esse token o Master não consegue chamar `api/master/*` no tenant, e
> suspensão, reativação e cobrança não funcionam.

Ative a **cobrança** quando desejar.

## 4. Criar o usuário administrador

```bash
cd /var/www/<site>
php8.3 artisan tinker
```

```php
\App\Models\User::create([
    'name'     => 'Nome do Cliente',
    'email'    => 'cliente@dominio.com.br',
    'password' => bcrypt('senha-forte-aqui'),
]);
```

## 5. Configurar a loja

Acesse `https://<dominio>/admin` → **Configurações** e preencha nome, slogan,
endereço, horário, logo, favicon e textos de SEO. É o que alimenta o site
público.

## 6. Verificar

- [ ] Home carrega com logo e favicon
- [ ] `/estoque` responde (vazio no começo)
- [ ] `/sitemap.xml` e `/robots.txt` respondem
- [ ] Login no `/admin` funciona
- [ ] **Upload de foto de ~6 MB** — valida o pool FPM dedicado
- [ ] Thumb gerado e visível
- [ ] `https://` sem aviso de certificado

O upload grande é o item que não se pula. O `php.ini` global do servidor tem
`upload_max_filesize = 2M`, insuficiente para foto de celular; quem levanta esse
teto para 20M é o pool dedicado que o script cria. Se falhar, o cliente descobre
no primeiro cadastro de veículo.

---

## Atualizar tenants existentes

Um por vez, com verificação:

```bash
/home/deploy/deploy-soavelveiculos.sh
/home/deploy/deploy-friedrichveiculos.sh
```

Cada deploy entra em modo de manutenção, atualiza código e dependências, roda
migrations, regenera caches, ajusta permissões e faz smoke test na home. Se
falhar em qualquer ponto, sai da manutenção sozinho e retorna erro — em vez de
imprimir "concluído" sobre um site quebrado.

Fonte: [`scripts/vps/deploy-tenant.sh`](../scripts/vps/deploy-tenant.sh)

## Remover um tenant

```bash
/home/deploy/provision-tenant.sh --destroy <site> --yes
```

Remove diretório, banco, usuário, pool e vhost. Os sites em produção estão numa
lista de protegidos dentro do script e não podem ser destruídos por acidente. O
certificado não é apagado — use `certbot delete --cert-name <dominio>`.

---

## Onde as coisas ficam

| | Caminho |
|---|---|
| Código | `/var/www/<site>` (dono `deploy:www-data`) |
| Scripts no servidor | `/home/deploy/` |
| Scripts versionados | [`scripts/vps/`](../scripts/vps/) neste repositório |
| Pool FPM | `/etc/php/8.3/fpm/pool.d/<site>.conf` |
| Vhost | `/etc/nginx/sites-available/<site>` |
| Log do app | `/var/www/<site>/storage/logs/laravel.log` |
| Log do pool | `/var/log/php8.3-fpm-<site>.log` |
| Log do nginx | `/var/log/nginx/<site>-{access,error}.log` |
| Senha do banco | `/root/migracao/<site>-dbpass.txt` (modo 600) |
| Backups | `/home/deploy/backups/` (cron diário às 3h, retenção 14 dias) |

## Cuidados no servidor

Ele hospeda **nove sites**, incluindo o Master que cobra os próprios tenants.
Mexer em configuração compartilhada afeta todos:

- **Sempre `php8.3`, nunca `php`.** O default do servidor é 8.4 e o app roda em
  8.3. Usar `php` puro faz o Composer resolver dependências e os caches serem
  gerados para uma versão diferente da que atende as requisições.
- **`nginx -t` e `php-fpm8.3 -t` antes de qualquer reload.** Um erro de sintaxe
  derruba todos os sites de uma vez.
- **Nunca alterar o `php.ini` global** para resolver limite de um site. Use pool
  dedicado, como o script faz.
- **Gerar caches antes de ajustar permissões.** Na ordem inversa os arquivos
  nascem com o umask da sessão; se ele for restritivo, o site retorna 500 com
  `ReflectionException: Class ` (nome vazio) e o `laravel.log` fica **vazio**,
  porque a falha ocorre antes do logger existir. Diagnóstico em um comando:
  `sudo -u www-data test -r bootstrap/cache/config.php && echo OK`
- **Cuidado com `umask` na sessão.** Um `umask 077` solto vale para todo arquivo
  criado depois dele naquele shell, e `chown` não conserta. Use em subshell:
  `( umask 077; comando )`
- **Após `systemctl reload nginx`, espere ~2s antes de testar.** Requisições
  imediatas podem retornar `000`, que é falha de transporte do cliente e não
  código HTTP.
