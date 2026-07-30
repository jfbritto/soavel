# Site institucional helpdiet.com.br — plano de implementação

> **Para trabalhadores agênticos:** OBRIGATÓRIO usar superpowers:subagent-driven-development (se houver subagentes) ou superpowers:executing-plans. As etapas usam checkbox (`- [ ]`).

**Objetivo:** publicar um site estático em `helpdiet.com.br` servido pelo VPS, substituindo o WordPress da hospedagem compartilhada, sem indisponibilidade.

**Arquitetura:** um `index.html` mais um `dist/style.css` gerado pelo Tailwind, versionados em repo público. O nginx do VPS serve o diretório direto, sem PHP e sem pool de FPM. O deploy é `git reset --hard`, sem `artisan down` — não há estado para preservar.

**Stack:** HTML, Tailwind CSS v4 compilado via `@tailwindcss/cli`, nginx, certbot.

**Spec:** `docs/superpowers/specs/2026-07-29-site-helpdiet-design.md`

**Notas de execução:**
- Cada etapa diz **onde roda**: `[MAC]`, `[VPS]`, `[GITHUB]` ou `[CLOUDFLARE]`.
- O Mac **não** alcança o VPS por SSH (conexão recusada na 22). Tudo que precisa chegar ao VPS vai por git.
- No VPS, sempre `php8.3` explícito onde houver PHP, e `nginx -t` antes de qualquer reload — 10 sites em produção compartilham aquele nginx.
- Verde da marca: `#3B7847`.

---

### Task 1: Repo, scaffold e pipeline do Tailwind

**Arquivos:**
- Criar: `~/Projetos/helpdiet-site/package.json`
- Criar: `~/Projetos/helpdiet-site/src/input.css`
- Criar: `~/Projetos/helpdiet-site/.gitignore`

- [ ] **Etapa 1 [GITHUB]: criar o repositório**

Criar `jfbritto/helpdiet-site` como **público**, sem README, sem `.gitignore` (vamos criar local).

Público é decisão do spec: o conteúdo é servido publicamente de qualquer forma, e repo público clona por HTTPS sem credencial — sem deploy key, sem alias `Host github-*` para manter.

- [ ] **Etapa 2 [MAC]: scaffold local**

```bash
mkdir -p ~/Projetos/helpdiet-site/{src,dist,assets}
cd ~/Projetos/helpdiet-site
git init -b main
git remote add origin https://github.com/jfbritto/helpdiet-site.git
```

- [ ] **Etapa 3 [MAC]: `.gitignore`**

`dist/` **não** entra aqui — o CSS compilado é versionado de propósito, para o VPS não precisar de node.

```
node_modules/
.DS_Store
```

- [ ] **Etapa 4 [MAC]: `package.json` com a versão fixada**

Fixar a major evita que um `npx` futuro troque o Tailwind por baixo e mude a saída sem aviso.

```json
{
  "name": "helpdiet-site",
  "private": true,
  "scripts": {
    "build": "npx @tailwindcss/cli -i src/input.css -o dist/style.css --minify",
    "watch": "npx @tailwindcss/cli -i src/input.css -o dist/style.css --watch"
  },
  "devDependencies": {
    "@tailwindcss/cli": "^4.0.0"
  }
}
```

- [ ] **Etapa 5 [MAC]: `src/input.css` com a paleta da marca**

No Tailwind v4 o tema é declarado em CSS, não em `tailwind.config.js`.

```css
@import "tailwindcss";

@theme {
  --color-brand-50:  #eef5f0;
  --color-brand-100: #d7e8dc;
  --color-brand-300: #8fcfa4;
  --color-brand-500: #3B7847;
  --color-brand-600: #2E5F38;
  --color-brand-900: #14532d;
}
```

- [ ] **Etapa 6 [MAC]: instalar e confirmar a versão**

```bash
cd ~/Projetos/helpdiet-site && npm install
npx @tailwindcss/cli --help | head -3
```

Esperado: ajuda do CLI sem erro. Se o comando não existir, o pacote mudou de nome — parar e reavaliar antes de seguir.

- [ ] **Etapa 7 [MAC]: commit**

```bash
git add -A && git commit -m "chore: scaffold do site com Tailwind v4 e paleta da marca"
```

---

### Task 2: Ativos otimizados

**Arquivos:**
- Criar: `assets/logo.png`, `assets/logo@2x.png`, `assets/favicon.png`, `assets/apple-touch-icon.png`, `assets/hero.webp`

- [ ] **Etapa 1 [MAC]: baixar os originais do site atual**

```bash
cd ~/Projetos/helpdiet-site
curl -s -o /tmp/logo-orig.png "https://helpdiet.com.br/wp-content/uploads/2025/03/logo.png"
curl -s -o assets/hero.webp "https://helpdiet.com.br/wp-content/uploads/2025/03/DALL·E-2025-03-22-19.03.43-Uma-nutricionista-profissional-usando-jaleco-branco-e-touca-higienica-preenchendo-uma-planilha-digital-em-um-tablet-em-uma-cozinha-profissional-mode-1536x878.webp"
ls -la /tmp/logo-orig.png assets/hero.webp
```

Esperado: logo com ~453 KB (877×870) e hero com ~99 KB.

- [ ] **Etapa 2 [MAC]: reduzir a logo**

O original tem 453 KB para desenhar um ícone de algumas dezenas de pixels na nav. Servi-lo cru anularia o ganho de sair do WordPress. `sips` é nativo do macOS — não há ImageMagick nesta máquina.

Nome `logo-2x.png` e não `logo@2x.png`: o `@` em URL dentro de `srcset` funciona, mas convida a problema de codificação sem oferecer nada em troca.

```bash
sips -Z 96  /tmp/logo-orig.png --out assets/logo.png       >/dev/null
sips -Z 192 /tmp/logo-orig.png --out assets/logo-2x.png    >/dev/null
sips -Z 32  /tmp/logo-orig.png --out assets/favicon.png    >/dev/null
sips -Z 180 /tmp/logo-orig.png --out assets/apple-touch-icon.png >/dev/null
```

- [ ] **Etapa 2b [MAC]: `robots.txt`**

O vhost referencia `/robots.txt`; sem o arquivo, todo rastreador gera um 404.

```bash
printf 'User-agent: *\nAllow: /\n' > robots.txt
```

- [ ] **Etapa 3 [MAC]: verificar o ganho**

```bash
ls -la assets/ | awk '{print "  ", $5, $9}'
echo "original: $(stat -f %z /tmp/logo-orig.png) bytes"
```

Esperado: `logo.png` na casa de poucos KB, não centenas. Se ainda estiver acima de ~20 KB, investigar antes de seguir.

- [ ] **Etapa 4 [MAC]: commit**

```bash
git add assets && git commit -m "feat: ativos otimizados (logo de 453 KB reduzida para uso em nav e favicon)"
```

---

### Task 3: A página

**Arquivos:**
- Criar: `index.html`

- [ ] **Etapa 1 [MAC]: cabeçalho e metadados**

O WordPress fornecia `title`, `description`, `og:*` e favicon de graça; HTML escrito à mão não. O `og:image` importa aqui porque o WhatsApp é o canal de contato — sem ele o link compartilhado aparece sem preview.

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>HelpDiet — controle de segurança alimentar para cozinhas profissionais</title>
<meta name="description" content="Solução para nutricionistas e gestores de cozinhas profissionais: fichas técnicas, controle de validade, planilhas digitais, relatórios e rastreabilidade.">
<link rel="icon" href="/assets/favicon.png">
<link rel="apple-touch-icon" href="/assets/apple-touch-icon.png">
<meta property="og:type" content="website">
<meta property="og:url" content="https://helpdiet.com.br/">
<meta property="og:title" content="HelpDiet — simplificando processos, garantindo segurança">
<meta property="og:description" content="Controle de segurança alimentar para cozinhas profissionais: validade, planilhas digitais, relatórios e rastreabilidade.">
<meta property="og:image" content="https://helpdiet.com.br/assets/hero.webp">
<link rel="stylesheet" href="/dist/style.css">
</head>
```

- [ ] **Etapa 2 [MAC]: nav fixa**

Colapsa no celular escondendo as âncoras e mantendo os dois CTAs, que são a razão de ser da página.

```html
<body class="antialiased text-slate-700">
<header class="sticky top-0 z-50 bg-brand-900 text-white">
  <nav class="mx-auto flex max-w-6xl items-center gap-6 px-5 py-3">
    <a href="/" class="flex items-center gap-2">
      <img src="/assets/logo.png" srcset="/assets/logo.png 1x, /assets/logo-2x.png 2x"
           alt="HelpDiet" width="32" height="32" class="h-8 w-8">
      <span class="text-lg font-bold">HelpDiet</span>
    </a>
    <div class="ml-auto hidden items-center gap-6 text-sm sm:flex">
      <a href="#recursos" class="text-brand-100 hover:text-white">Recursos</a>
      <a href="#sobre" class="text-brand-100 hover:text-white">Sobre</a>
    </div>
    <a href="https://planilhas.helpdiet.com.br"
       class="rounded-md border border-brand-300 px-3 py-1.5 text-sm font-semibold text-white hover:bg-white/10">Entrar</a>
    <a href="https://api.whatsapp.com/send?phone=5528999743099&amp;text=Ol%C3%A1,%20estou%20interessado%20no%20HelpDiet!"
       class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-brand-900 hover:bg-brand-50">WhatsApp</a>
  </nav>
</header>
```

- [ ] **Etapa 3 [MAC]: hero (layout C do spec)**

O eyebrow **não** repete "Gestão Inteligente", que é o título do recurso 1 — repetir a frase acima do grid que a contém fica redundante. A headline é a literal do site atual.

```html
<section class="bg-gradient-to-br from-brand-900 via-brand-500 to-brand-300">
  <div class="mx-auto grid max-w-6xl items-center gap-10 px-5 py-16 lg:grid-cols-2 lg:py-24">
    <div>
      <span class="inline-block rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wider text-brand-50">
        Segurança alimentar para cozinhas profissionais
      </span>
      <h1 class="mt-4 text-4xl font-extrabold leading-tight tracking-tight text-white lg:text-5xl">
        Simplificando processos, garantindo segurança.
      </h1>
      <p class="mt-4 max-w-xl text-lg leading-relaxed text-brand-50/90">
        O HelpDiet é uma solução inteligente desenvolvida para nutricionistas e gestores de
        cozinhas profissionais que desejam simplificar o controle da segurança alimentar,
        reduzir riscos e otimizar processos. Com o HelpDiet, sua cozinha estará sempre segura,
        organizada e em conformidade com as normas vigentes.
      </p>
      <div class="mt-8 flex flex-wrap gap-3">
        <a href="https://api.whatsapp.com/send?phone=5528999743099&amp;text=Ol%C3%A1,%20estou%20interessado%20no%20HelpDiet!"
           class="rounded-lg bg-white px-6 py-3 font-semibold text-brand-900 shadow-lg hover:bg-brand-50">Falar no WhatsApp</a>
        <a href="https://planilhas.helpdiet.com.br"
           class="rounded-lg border border-brand-300 px-6 py-3 font-semibold text-white hover:bg-white/10">Entrar no sistema</a>
      </div>
    </div>
    <img src="/assets/hero.webp" width="1536" height="878" loading="eager"
         alt="Nutricionista preenchendo uma planilha digital em um tablet em cozinha profissional"
         class="w-full rounded-xl shadow-2xl">
  </div>
</section>
```

- [ ] **Etapa 4 [MAC]: grid dos 6 recursos**

Texto literal do spec. O grid vai de 3 colunas para 2 e depois 1.

```html
<section id="recursos" class="mx-auto max-w-6xl px-5 py-16 lg:py-24">
  <h2 class="text-3xl font-bold tracking-tight text-brand-900">Recursos</h2>
  <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <!-- repetir este bloco para os 6, com titulo e texto do spec -->
    <div class="rounded-xl border border-slate-200 p-6">
      <h3 class="font-bold text-brand-900">Gestão Inteligente</h3>
      <p class="mt-2 text-sm leading-relaxed">Organize e padronize todas as receitas e preparações
      em fichas técnicas digitais detalhadas, garantindo fácil acesso e atualização imediata.</p>
    </div>
  </div>
</section>
```

Os seis, na ordem do spec: Gestão Inteligente, Controle de Validade, Planilhas Digitais Integradas, Relatórios Instantâneos, Alertas e Notificações em Tempo Real, Rastreamento Completo dos Alimentos.

- [ ] **Etapa 5 [MAC]: Sobre Nós e rodapé**

Sem link de Instagram: o do site atual é `href="#"`, nunca configurado, e link morto não se carrega para o site novo.

```html
<section id="sobre" class="bg-brand-50">
  <div class="mx-auto max-w-3xl px-5 py-16 lg:py-24">
    <h2 class="text-3xl font-bold tracking-tight text-brand-900">Sobre Nós</h2>
    <p class="mt-6 text-lg leading-relaxed">Criado com o objetivo de transformar a gestão da
    segurança alimentar, o HelpDiet nasceu para facilitar e modernizar o controle de processos
    em cozinhas profissionais. Nossa missão é proporcionar segurança, eficiência e tranquilidade
    para nutricionistas, estabelecimentos e clientes finais.</p>
  </div>
</section>

<footer class="bg-brand-900 text-brand-100">
  <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-4 px-5 py-10 text-sm">
    <img src="/assets/logo.png" alt="" width="28" height="28" class="h-7 w-7">
    <span class="font-semibold text-white">HelpDiet</span>
    <div class="ml-auto flex gap-5">
      <a href="https://api.whatsapp.com/send?phone=5528999743099" class="hover:text-white">WhatsApp</a>
      <a href="https://planilhas.helpdiet.com.br" class="hover:text-white">Acessar o sistema</a>
    </div>
  </div>
</footer>
</body>
</html>
```

- [ ] **Etapa 6 [MAC]: commit**

```bash
git add index.html && git commit -m "feat: pagina institucional com o conteudo atual e metadados"
```

---

### Task 4: Build e verificação local

- [ ] **Etapa 1 [MAC]: compilar**

```bash
cd ~/Projetos/helpdiet-site && npm run build
ls -la dist/style.css
```

Esperado: arquivo gerado, na casa de poucos KB. Se passar de ~50 KB, o Tailwind não está removendo classes não usadas — investigar antes de seguir.

- [ ] **Etapa 2 [MAC]: servir e conferir**

O `python3` desta máquina é um shim do asdf sem versão definida e falha com "No version is set" — usar `npx serve`.

```bash
cd ~/Projetos/helpdiet-site && npx serve -l 8099 >/dev/null 2>&1 &
sleep 3
curl -s -o /dev/null -w 'HTTP %{http_code}\n' http://localhost:8099/
curl -s -o /dev/null -w 'CSS  HTTP %{http_code}  %{size_download} bytes\n' http://localhost:8099/dist/style.css
```

Esperado: 200 nos dois. **CSS em 404 é o erro mais provável desta etapa** — significa caminho errado no `<link>`, e a página abriria sem estilo nenhum.

- [ ] **Etapa 2b [MAC]: confirmar que as classes do gradiente existem na saída**

O Tailwind v4 **renomeou** as utilidades de gradiente: `bg-gradient-to-br` virou `bg-linear-to-br`. Classe inexistente não gera erro de build — o Tailwind simplesmente não a emite, e o hero renderiza chapado em vez de com gradiente. É falha silenciosa, então tem que ser verificada explicitamente.

```bash
grep -c 'linear-gradient' dist/style.css
```

Esperado: pelo menos 1. Se der `0`, trocar no `index.html`:

```
bg-gradient-to-br  ->  bg-linear-to-br
```

e recompilar. Conferir também no navegador que a faixa do hero tem gradiente, não cor única.

- [ ] **Etapa 3 [MAC]: conferir no navegador**

Abrir `http://localhost:8099/`. Verificar: estilo aplicado (não texto puro), âncoras `Recursos` e `Sobre` rolando, os dois CTAs abrindo o destino certo, e o layout empilhando ao estreitar a janela até largura de celular.

- [ ] **Etapa 4 [MAC]: parar o servidor e commitar**

```bash
kill %1 2>/dev/null
git add dist/style.css && git commit -m "build: css compilado (versionado para o VPS nao precisar de node)"
git push -u origin main
```

---

### Task 5: Ajustar o hook do ACME e emitir o certificado

**Arquivos:**
- Modificar: `/root/migracao/acme-auth.sh` (VPS)

- [ ] **Etapa 1 [VPS]: entender o problema antes de mexer**

```bash
grep -A5 'case "$CERTBOT_DOMAIN"' /root/migracao/acme-auth.sh
ssh -o BatchMode=yes -i /root/.ssh/id_migracao helpdi71@108.167.132.218 'ls -d ~/helpdiet.com.br ~/public_html 2>&1'
```

Esperado: `~/helpdiet.com.br` **não existe**; `~/public_html` existe. O hook deriva o diretório do nome do domínio, então falharia no apex.

- [ ] **Etapa 2 [VPS]: adicionar o caso do apex**

```bash
cp -a /root/migracao/acme-auth.sh /root/migracao/acme-auth.sh.bak
python3 - <<'PY'
p='/root/migracao/acme-auth.sh'
s=open(p).read()
old='''case "$CERTBOT_DOMAIN" in
  www.*) DIR="${CERTBOT_DOMAIN#www.}" ;;'''
new='''case "$CERTBOT_DOMAIN" in
  # helpdiet.com.br e o dominio principal da conta cPanel: o docroot dele e
  # ~/public_html, nao ~/helpdiet.com.br (que nao existe).
  helpdiet.com.br|www.helpdiet.com.br) DIR="public_html" ;;
  www.*) DIR="${CERTBOT_DOMAIN#www.}" ;;'''
assert old in s, "trecho nao encontrado — inspecionar o script antes de seguir"
open(p,'w').write(s.replace(old,new))
PY
bash -n /root/migracao/acme-auth.sh && echo "sintaxe ok"
grep -A7 'case "$CERTBOT_DOMAIN"' /root/migracao/acme-auth.sh
```

- [ ] **Etapa 3 [VPS]: testar o hook com valores falsos antes de gastar tentativa**

Falha de validação conta no limite do Let's Encrypt. O hook verifica a URL pública com o User-Agent real antes de liberar, então este teste exercita a cadeia inteira.

```bash
CERTBOT_DOMAIN=helpdiet.com.br \
CERTBOT_TOKEN="preflight-$(date +%s)" \
CERTBOT_VALIDATION="valor-de-teste" \
/root/migracao/acme-auth.sh; echo "exit: $?"
```

Esperado: `[hook] desafio publicado e verificado: helpdiet.com.br` e exit 0.

Se falhar, a causa provável é o WordPress interceptando `/.well-known/`. O `.htaccess` dele só reescreve o que não é arquivo real, então deveria passar — mas se não passar, adicionar uma exceção no `.htaccess` do `public_html`.

- [ ] **Etapa 4 [VPS]: emitir o certificado**

```bash
certbot certonly --manual --preferred-challenges http \
  --manual-auth-hook /root/migracao/acme-auth.sh \
  --manual-cleanup-hook /root/migracao/acme-cleanup.sh \
  -d helpdiet.com.br -d www.helpdiet.com.br \
  --non-interactive --agree-tos
```

Esperado: `Successfully received certificate` e os dois nomes listados.

- [ ] **Etapa 5 [VPS]: limpar o diretório espúrio**

O hook publica em `~/$DIR` **e** `~/$DIR/public`; com `DIR=public_html` isso cria um `~/public_html/public` que não serve para nada.

```bash
ssh -o BatchMode=yes -i /root/.ssh/id_migracao helpdi71@108.167.132.218 \
  'rm -rf ~/public_html/public/.well-known; rmdir ~/public_html/public 2>/dev/null; ls -d ~/public_html/public 2>&1'
```

Esperado: "No such file or directory".

---

### Task 6: Publicar no VPS e criar o vhost

- [ ] **Etapa 1 [VPS]: clonar como `deploy`, não como root**

`/var/www` não é gravável pelo `deploy`, então o diretório é criado por root com o dono certo e o clone roda como `deploy`. Clonar como root deixa `.git` com arquivos de root e o deploy seguinte falha com `Permission denied` — foi o que aconteceu no planilhasnutri.

```bash
install -d -o deploy -g www-data -m 755 /var/www/helpdiet
sudo -u deploy git clone https://github.com/jfbritto/helpdiet-site.git /var/www/helpdiet
sudo -u deploy git -C /var/www/helpdiet log --oneline -1
ls -la /var/www/helpdiet/ | head
```

- [ ] **Etapa 2 [VPS]: permissões**

```bash
chown -R deploy:www-data /var/www/helpdiet
find /var/www/helpdiet -type d -not -path '*/.git/*' -exec chmod 755 {} \;
find /var/www/helpdiet -type f -not -path '*/.git/*' -exec chmod 644 {} \;
echo "www-data le o index? $(sudo -u www-data test -r /var/www/helpdiet/index.html && echo SIM || echo NAO)"
echo "www-data le o css?   $(sudo -u www-data test -r /var/www/helpdiet/dist/style.css && echo SIM || echo NAO)"
```

- [ ] **Etapa 3 [VPS]: vhost**

Sem `fastcgi_pass` e sem pool de FPM — nenhum processo PHP envolvido.

```bash
cat > /etc/nginx/sites-available/helpdiet <<'EOF'
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name helpdiet.com.br;
    root /var/www/helpdiet;
    index index.html;

    charset utf-8;
    ssl_certificate /etc/letsencrypt/live/helpdiet.com.br/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/helpdiet.com.br/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / { try_files $uri $uri/ =404; }

    # o css e os ativos sao versionados no git e trocam so em deploy
    location ~* \.(css|js|png|webp|svg|ico|woff2?)$ {
        expires 30d;
        add_header Cache-Control "public";
        access_log off;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    location ~ /\.(?!well-known).* { deny all; }

    access_log /var/log/nginx/helpdiet-access.log;
    error_log  /var/log/nginx/helpdiet-error.log;
}

# www redireciona para o apex, que e o comportamento de hoje no compartilhado
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name www.helpdiet.com.br;
    ssl_certificate /etc/letsencrypt/live/helpdiet.com.br/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/helpdiet.com.br/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
    return 301 https://helpdiet.com.br$request_uri;
}

server {
    listen 80;
    listen [::]:80;
    server_name helpdiet.com.br www.helpdiet.com.br;
    return 301 https://helpdiet.com.br$request_uri;
}
EOF

ln -sfn /etc/nginx/sites-available/helpdiet /etc/nginx/sites-enabled/helpdiet
nginx -t && systemctl reload nginx && sleep 2 && echo "reload OK"
```

O `&&` encadeado garante que o reload não roda se o teste falhar.

- [ ] **Etapa 4 [VPS]: confirmar que o nginx carregou o vhost**

`nginx -t` passar não prova que o novo bloco entrou — passaria igual se o symlink não existisse.

```bash
nginx -T 2>/dev/null | grep -c 'helpdiet.com.br'
ls -la /etc/nginx/sites-enabled/helpdiet
```

Esperado: contagem maior que zero e o symlink presente.

- [ ] **Etapa 5 [VPS]: nenhuma regressão nos outros sites**

`grep -R` e não `-r`: `sites-enabled/` é cheio de symlinks, e o `-r` **não os segue**.

```bash
for D in $(grep -RhoP '^\s*server_name\s+\K[^;]+' /etc/nginx/sites-enabled/ | tr ' ' '\n' \
           | grep '\.' | grep -v '^\*' | sort -u); do
  printf "  %-34s %s\n" "$D" "$(curl -sk -o /dev/null -w '%{http_code}' --max-time 10 https://$D/)"
done
```

Esperado: todos em 200, 301 ou 302.

---

### Task 7: Script de deploy para sites estáticos

**Arquivos:**
- Criar: `scripts/vps/deploy-static.sh` (no repo da soavel, onde os outros scripts vivem)
- Instalar: `/home/deploy/deploy-static.sh` (VPS)

- [ ] **Etapa 1 [MAC]: escrever o script**

Genérico como o `deploy-tenant.sh`, para servir futuros sites estáticos. Sem `artisan down`, sem composer, sem migrations, sem caches — e por isso **sem indisponibilidade**.

```bash
#!/bin/bash
# =============================================================================
# deploy-static.sh — deploy de um site estatico no VPS
#   Uso: deploy-static.sh <site> <dominio>
# Rodar como root (faz chown e le o nginx).
# =============================================================================
set -euo pipefail

SITE="${1:?falta o nome do site}"
DOMAIN="${2:?falta o dominio}"
APP="/var/www/$SITE"
LOCK="/tmp/deploy-$SITE.lock"

[ -d "$APP" ] || { echo "ERRO: $APP nao existe"; exit 1; }
exec 9>"$LOCK"; flock -n 9 || { echo "Deploy de $SITE ja em andamento."; exit 1; }

echo "==> Deploy de $SITE ($DOMAIN) — $(date '+%F %T')"

# git como deploy: e o dono do repo. Como root o git recusa operar em repo de
# outro usuario e deixa .git/FETCH_HEAD com dono errado.
sudo -u deploy git -C "$APP" fetch origin main
sudo -u deploy git -C "$APP" reset --hard origin/main
sudo -u deploy git -C "$APP" log --oneline -1

chown -R deploy:www-data "$APP"
find "$APP" -type d -not -path '*/.git/*' -exec chmod 755 {} \;
find "$APP" -type f -not -path '*/.git/*' -exec chmod 644 {} \;

CODE=$(curl -so /dev/null -w '%{http_code}' --max-time 25 "https://$DOMAIN/")
echo "    home: HTTP $CODE"
[ "$CODE" = "200" ] || { echo "FALHOU: home retornou $CODE"; exit 1; }

echo "==> Deploy de $SITE concluido"
```

- [ ] **Etapa 2 [MAC]: commit e push no repo da soavel**

```bash
cd ~/Projetos/site-carros/soavel
chmod 755 scripts/vps/deploy-static.sh
git add scripts/vps/deploy-static.sh
git commit -m "feat: deploy-static.sh para sites sem PHP (sem down, sem composer, sem caches)"
git push origin main
```

- [ ] **Etapa 3 [VPS]: instalar e conferir que não divergiu do repo**

Cópia do servidor divergente do repo faz perder o rastro do que está em produção.

O VPS não tem o repo da soavel clonado num lugar de onde dê para copiar sem arrastar o app junto, e o Mac não alcança o VPS por SSH. Então o arquivo é reescrito no VPS por heredoc — e o `md5` é o que garante que nenhum caractere se perdeu na transcrição.

```bash
# no MAC, obter o md5 de referencia:
md5 -q ~/Projetos/site-carros/soavel/scripts/vps/deploy-static.sh

# no VPS, escrever o arquivo com heredoc citado ('SCRIPT' entre aspas simples
# preserva o conteudo literalmente, sem expandir $VARIAVEIS):
cat > /home/deploy/deploy-static.sh <<'SCRIPT'
...conteudo identico ao do repo, byte a byte...
SCRIPT
chmod 755 /home/deploy/deploy-static.sh
md5sum /home/deploy/deploy-static.sh | cut -d' ' -f1
```

Esperado: os dois md5 idênticos. Se divergirem, **parar** — script de deploy diferente do versionado faz perder o rastro do que está em produção.

---

### Task 8: Validar pelo `/etc/hosts`, antes de qualquer DNS

- [ ] **Etapa 1 [MAC]: apontar só esta máquina para o VPS**

```bash
printf '\n# SITE-HELPDIET (remover depois do corte)\n129.121.50.200\thelpdiet.com.br\n129.121.50.200\twww.helpdiet.com.br\n' | sudo tee -a /etc/hosts
sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder
```

- [ ] **Etapa 2 [MAC]: certificado válido nos dois nomes**

`--cacert` e não `-k`: o `-k` esconderia falha de cadeia, e erro de certificado é o único problema que o visitante vê como tela vermelha.

```bash
for H in helpdiet.com.br www.helpdiet.com.br; do
  printf "  %-24s " "$H"
  curl -s -o /dev/null -w 'HTTP %{http_code}  redirect=%{redirect_url}\n' "https://$H/"
done
```

Esperado: apex em 200 e `www` em 301 para o apex, **sem erro de TLS em nenhum dos dois**.

- [ ] **Etapa 3 [MAC]: conferir no navegador**

Em `https://helpdiet.com.br`: estilo aplicado, âncoras rolando, os dois CTAs corretos, layout empilhando em largura de celular, favicon aparecendo na aba.

- [ ] **Etapa 4 [MAC]: preview de link no WhatsApp**

Mandar `https://helpdiet.com.br` para si mesmo. **Isto vai falhar neste momento e é esperado**: o WhatsApp resolve pelo DNS público, que ainda aponta para o WordPress. O teste real é depois da troca — a etapa existe aqui só para registrar que o `og:image` precisa ser conferido depois.

---

### Task 9: Trocar o DNS

- [ ] **Etapa 1 [CLOUDFLARE]: baixar o TTL antes de trocar**

Em `helpdiet.com.br` → DNS → Records, editar o A do apex: TTL de `Auto`/`14400` para **60**, sem mudar o IP ainda.

Se o TTL for baixado só na hora da troca, resolvers que já cachearam o valor antigo o mantêm por horas — foi o que fez a troca do `planilhas` propagar em segundos quando o TTL já estava em 60.

- [ ] **Etapa 2 [VPS]: confirmar que o TTL 60 propagou**

```bash
for NS in 8.8.8.8 1.1.1.1 9.9.9.9; do
  printf "  %-8s %s\n" "$NS" "$(dig +noall +answer A helpdiet.com.br @$NS | awk '{print "ttl="$2"  ip="$5}')"
done
```

Esperado: `ttl` ≤ 60 nos três, ainda com `ip=108.167.132.218`. Só seguir quando os três estiverem em 60.

- [ ] **Etapa 2b [VPS]: registrar os CNAMEs antes de apagá-los**

Apagar sem anotar deixa o rollback incompleto — não há como recriar o que não foi registrado.

```bash
for H in ftp cpanel webmail; do
  printf "  %-10s CNAME -> %s\n" "$H" \
    "$(dig +short CNAME $H.helpdiet.com.br @poppy.ns.cloudflare.com)"
done | tee /root/migracao/cnames-helpdiet-antes.txt
```

Esperado: os três como CNAME para `helpdiet.com.br.`

- [ ] **Etapa 3 [CLOUDFLARE]: trocar o A e apagar os CNAMEs**

- `helpdiet.com.br` A: `108.167.132.218` → **`129.121.50.200`**, mantendo `DNS only` (nuvem cinza)
- **apagar** `ftp`, `cpanel`, `webmail`

O `www` é CNAME para o apex e **acompanha sozinho** — não mexer nele.

Os três apagados são CNAME para o apex; sem apagar, passariam a resolver para o VPS, que não atende webmail nem FTP. Verificado que nada depende deles: `cpanel` e `webmail` já retornam HTTP 500 hoje, e o cPanel tem duas vias funcionando (`108.167.132.218:2083` e `cpanel.brunellinutri.com.br`).

- [ ] **Etapa 4 [VPS]: acompanhar a propagação**

```bash
for i in $(seq 1 20); do
  A=$(dig +short A helpdiet.com.br @8.8.8.8 | head -1)
  B=$(dig +short A helpdiet.com.br @1.1.1.1 | head -1)
  C=$(dig +short A helpdiet.com.br @9.9.9.9 | head -1)
  printf "[%s] %s %s %s  HTTP=%s\n" "$(date +%H:%M:%S)" "$A" "$B" "$C" \
    "$(curl -s -o /dev/null -w '%{http_code}' --max-time 8 https://helpdiet.com.br/)"
  [ "$A" = "129.121.50.200" ] && [ "$B" = "129.121.50.200" ] && [ "$C" = "129.121.50.200" ] && \
    { echo ">>> propagado"; curl -sI https://helpdiet.com.br/ | grep -iE '^(HTTP|server)'; break; }
  sleep 10
done
```

Esperado: os três no IP do VPS e `server: nginx` — o compartilhado é Apache, então o header prova quem está atendendo.

---

### Task 10: Fechar

- [ ] **Etapa 1 [VPS]: renovação automática pelo nginx**

Os hooks manuais publicam no **compartilhado** e param de funcionar agora que o domínio aponta para o VPS. Sem esta troca a renovação falha calada e só aparece em 60 dias.

```bash
cat /etc/letsencrypt/renewal/helpdiet.com.br.conf
sed -i -e 's/^authenticator = manual/authenticator = nginx/' \
       -e '/^manual_auth_hook/d' -e '/^manual_cleanup_hook/d' -e '/^pref_challs/d' \
  /etc/letsencrypt/renewal/helpdiet.com.br.conf
certbot renew --cert-name helpdiet.com.br --dry-run 2>&1 | tail -6
```

Esperado: `all simulated renewals succeeded`.

- [ ] **Etapa 2 [MAC]: remover o `/etc/hosts`**

Sem isto tu continuas testando um caminho que não é o dos visitantes.

```bash
sudo sed -i '' '/SITE-HELPDIET/,+2d' /etc/hosts
sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder
grep -c helpdiet /etc/hosts
```

Esperado: `0`.

- [ ] **Etapa 3 [MAC]: preview no WhatsApp, agora de verdade**

Mandar `https://helpdiet.com.br` para si mesmo e confirmar título, descrição e imagem no preview. Se a imagem não aparecer, conferir se o `og:image` está com URL absoluta e acessível.

- [ ] **Etapa 4 [VPS]: os três nomes apagados deixaram de resolver**

```bash
for H in ftp.helpdiet.com.br cpanel.helpdiet.com.br webmail.helpdiet.com.br; do
  printf "  %-28s %s\n" "$H" "$(dig +short A $H @1.1.1.1 | head -1 || true)"
done
echo "  (vazio nos tres = correto)"
```

- [ ] **Etapa 5 [VPS]: testar o deploy de ponta a ponta**

Fazer uma alteração trivial no repo (ex.: um espaço no `index.html`), commitar, empurrar, e rodar:

```bash
/home/deploy/deploy-static.sh helpdiet helpdiet.com.br
```

Esperado: o commit novo aparecendo e `home: HTTP 200`. Isto confirma que o caminho de atualização funciona **antes** de tu precisares dele com urgência.

- [ ] **Etapa 6 [VPS]: backup com o site em produção**

```bash
/home/deploy/backup.sh && ls -lat /home/deploy/backups/ | head -4
```

O `backup.sh` varre `/var/www/*/` procurando `storage/app`; um site estático não tem, então ele é **ignorado** pelo backup — o que é correto, porque o conteúdo dele vive no git. Nada a fazer, só registrar que é intencional.

---

## O que este plano deliberadamente não faz

- **Não apaga o WordPress.** Ele continua no compartilhado servindo, apenas sem receber tráfego, como rede de rollback até o cancelamento do plano M. O arquivamento (dump do banco + `wp-content/uploads`) é item separado, da lista de agosto.
- **Não mexe no SPF nem no MX** do helpdiet. Depois da troca, o mecanismo `a` do SPF passa a autorizar o VPS, o que é inofensivo porque nada envia de lá — o único e-mail do produto é o reset de senha, que sai pelo Resend. Limpar entra na lista do cancelamento.
- **Não troca a foto do hero**, apesar do texto embaralhado de IA no bordado do jaleco. É independente de tudo o mais e pode ser feito depois com um commit.

## Rollback

Devolver o A do apex para `108.167.132.218` no Cloudflare. Com TTL 60, volta em cerca de um minuto, e o WordPress atende como se nada tivesse acontecido. Os três CNAMEs apagados precisariam ser recriados se o rollback for para valer — anotar os valores antes de apagar (todos eram CNAME para `helpdiet.com.br`).
