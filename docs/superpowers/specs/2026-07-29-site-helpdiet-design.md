# Site institucional `helpdiet.com.br` — desenho

**Data:** 2026-07-29
**Motivação:** o apex `helpdiet.com.br` roda WordPress na hospedagem compartilhada HostGator, que será cancelada até o fim de agosto/2026. O site precisa sair de lá.

## Situação atual

| | |
|---|---|
| Plataforma | WordPress 7.0.2 em `108.167.132.218`, docroot `~/public_html` |
| Estrutura | **uma única página**; nenhuma página interna (os únicos links são `feed/`, `wp-json/`, `xmlrpc.php`) |
| Peso | 88.575 bytes de HTML, mais o CSS e JS do WordPress |
| Formulários | nenhum |
| CTA | um só: WhatsApp para `5528999743099` |
| Link de Instagram | existe no cabeçalho e no rodapé, mas com `href="#"` — nunca foi configurado |
| `www` | CNAME para o apex; o Apache faz 301 para o apex |
| Logo | `logo.png`, 877×870, verde **`#3B7847`** (amostragem: valores agrupados em `#3c7847`/`#3a7947`) |
| Hero | webp 1536×878, gerado por IA: profissional de jaleco com tablet em cozinha industrial |

## Objetivo

Reproduzir o conteúdo atual fora do WordPress, com a linguagem visual do `treinaedu.com.br`, acrescentando duas ações que hoje não existem: **link de login** para `planilhas.helpdiet.com.br` e o **WhatsApp** em posição de destaque.

**Em escopo:** o conteúdo que já existe, modernizado.
**Fora de escopo:** preços, FAQ, prova social, formulário, cadastro autosserviço, blog, páginas internas.

## Conteúdo (texto literal do site atual)

**Tagline / H1:** Simplificando processos, garantindo segurança.

**Abertura:** O HelpDiet é uma solução inteligente desenvolvida para nutricionistas e gestores de cozinhas profissionais que desejam simplificar o controle da segurança alimentar, reduzir riscos e otimizar processos. Com o HelpDiet, sua cozinha estará sempre segura, organizada e em conformidade com as normas vigentes.

**Os seis recursos:**

1. **Gestão Inteligente** — Organize e padronize todas as receitas e preparações em fichas técnicas digitais detalhadas, garantindo fácil acesso e atualização imediata.
2. **Controle de Validade** — Receba alertas antecipados sobre produtos próximos do vencimento, evitando desperdícios e garantindo a segurança dos alimentos.
3. **Planilhas Digitais Integradas** — Elimine o papel com planilhas digitais integradas que facilitam o preenchimento, análise e acompanhamento dos processos de segurança alimentar.
4. **Relatórios Instantâneos** — Gere relatórios completos e automáticos sobre temperatura, higiene, validade e rastreabilidade dos alimentos, simplificando auditorias internas e externas.
5. **Alertas e Notificações em Tempo Real** — Fique sempre informado com notificações automáticas sobre pendências importantes, certificados prestes a vencer e atividades que exigem atenção imediata.
6. **Rastreamento Completo dos Alimentos** — Tenha um histórico completo e detalhado da procedência e movimentação dos alimentos, assegurando transparência e conformidade com as normas sanitárias.

**Sobre Nós:** Criado com o objetivo de transformar a gestão da segurança alimentar, o HelpDiet nasceu para facilitar e modernizar o controle de processos em cozinhas profissionais. Nossa missão é proporcionar segurança, eficiência e tranquilidade para nutricionistas, estabelecimentos e clientes finais.

## Decisões

**Estático com Tailwind compilado.** Sem PHP, sem banco, sem pool de FPM. O nginx entrega os arquivos direto.

O que decidiu: o VPS tem 3,9 GB de RAM e já compromete 41 filhos de PHP-FPM entre os 10 sites — um site estático consome **zero**. E o `deploy-tenant.sh` (que faz `artisan down`, `composer install`, `migrate` e três caches) não tem nada a fazer aqui: o deploy é um `git reset --hard` e ponto, **sem indisponibilidade**.

O Tailwind é compilado no Mac e o `.css` de saída é versionado. Descartada a variante por CDN: cria dependência de host externo em produção e bloqueia a primeira renderização — o site ficaria mais lento que o WordPress que está substituindo.

**Repo público, remote HTTPS, sem deploy key.** O conteúdo é HTML, CSS e duas imagens — tudo servido publicamente de qualquer forma. Não há `.env`, credencial ou lógica de negócio. Repo público clona por HTTPS sem autenticação, então não há chave nem alias `Host github-*` para criar, rotacionar ou perder. É como `soavelveiculos` e `friedrichveiculos` já funcionam.

**Paleta verde da marca, estrutura do treinaedu.** O `treinaedu.com.br` é Tailwind com indigo/violeta (`#6366f1`, `#4f46e5`, `#8b5cf6`). Aqui se herda a *linguagem* — nav fixa com âncoras, tipografia forte, espaçamento generoso, gradiente no hero — mas com o `#3B7847` da logo. Indigo brigaria com a marca.

**Hero em faixa verde com a foto em card** (opção C entre três mockups avaliados). O verde ocupa o topo inteiro e a foto flutua num card com sombra.

## Estrutura da página

Um `index.html`, nesta ordem:

1. **Nav fixa** — logo + "HelpDiet", âncoras `Recursos` e `Sobre`, botão fantasma `Entrar`, botão sólido `WhatsApp`
2. **Hero** — faixa com gradiente verde (`#14532d` → `#3B7847` → `#4e9160`), eyebrow, o H1 "Simplificando processos, garantindo segurança.", o parágrafo de abertura, os dois CTAs, e a foto num card à direita
3. **Recursos** — grid dos 6 blocos
4. **Sobre Nós** — o parágrafo institucional
5. **Rodapé** — logo, WhatsApp, link para `planilhas.helpdiet.com.br`

`Entrar` aponta para `https://planilhas.helpdiet.com.br`. `WhatsApp` reusa a URL atual, com o mesmo texto pré-preenchido.

## Infraestrutura

- **Repo:** novo, público, remote HTTPS
- **VPS:** `/var/www/helpdiet`, dono `deploy:www-data`, docroot na raiz (não há `public/`)
- **nginx:** `server_name helpdiet.com.br www.helpdiet.com.br`; `try_files`; **sem `fastcgi_pass`**; `www` responde 301 para o apex
- **Certificado:** um só, cobrindo apex e `www`
- **Deploy:** `sudo -u deploy git fetch origin main && git reset --hard origin/main`

## Build

`npx tailwindcss` no Mac, com o verde e seus tons declarados no tema, saída minificada em `dist/style.css`, versionada. Só as classes usadas entram. O VPS nunca precisa de node.

## Cutover

Este caso é mais simples que as migrações anteriores porque **não há dado, sessão nem estado** — nada a dumpar, nada a sincronizar, ninguém para deslogar. Não há janela de manutenção e não precisa de madrugada.

1. Criar o repo, construir o site, clonar em `/var/www/helpdiet`
2. Emitir o certificado para apex + `www` — **ver a ressalva do hook ACME abaixo**
3. Criar o vhost, `nginx -t`, reload
4. Validar pelo `/etc/hosts` do Mac, antes de qualquer mudança de DNS
5. Baixar o TTL do A do apex de 14400 para 60 e esperar a propagação
6. No Cloudflare: apontar o A do apex para `129.121.50.200` e **apagar** `ftp`, `cpanel` e `webmail`
7. Verificar apex e `www` nos três resolvers públicos

O `www` é CNAME para o apex, então **acompanha sozinho** — não precisa de alteração própria.

## Rollback

Devolver o A do apex para `108.167.132.218`. O WordPress não é apagado na troca: continua no compartilhado, servindo, apenas sem receber tráfego. Ele permanece como rede de segurança até o cancelamento do plano M.

## Riscos e pontos de atenção

**O hook do ACME não serve para o apex sem ajuste.** `/root/migracao/acme-auth.sh` deriva o diretório do nome do domínio: para `helpdiet.com.br` ele tentaria `~/helpdiet.com.br` no compartilhado, que **não existe** — o docroot do apex é `~/public_html`. Sem ajustar, a emissão falha. Alternativa é emitir depois da troca de DNS, mas aí existe uma janela em que o site responde com erro de TLS, que o visitante vê como tela vermelha de aviso. **Decisão: ajustar o hook e emitir antes.**

**Trocar o A do apex arrasta os CNAMEs.** `ftp`, `cpanel`, `webmail` e `www` são CNAME para o apex. Mover o apex leva os quatro. O `www` é desejado; os outros três, não — e por isso são apagados no passo 6. Verificado que nada depende deles: `cpanel` e `webmail` **já retornam HTTP 500 hoje**, e o cPanel tem duas vias independentes funcionando (`108.167.132.218:2083` e `cpanel.brunellinutri.com.br`, ambas em 200). A porta 21 está aberta com Pure-FTPd, mas cliente de FTP conecta por host:21, não por HTTP, e toda a migração foi feita por SSH.

**O mecanismo `a` do SPF muda de significado.** O SPF do apex é `v=spf1 a mx include:websitewelcome.com ~all`; o `a` autoriza o IP do A do apex a enviar e-mail. Depois da troca ele passa a autorizar o VPS. É inofensivo porque nada envia de lá — o único e-mail do produto é o reset de senha do Laravel, que sai pelo Resend via `send.helpdiet.com.br`. Limpar o SPF entra na lista do cancelamento, não desta entrega.

**A foto do hero tem artefato de IA.** O bordado do jaleco traz texto embaralhado, visível de perto. Aceitável no tamanho em que aparece; se incomodar, trocar a imagem é independente do resto.

## Pontos abertos

- **Instagram:** o link atual é `href="#"`, nunca configurado. Não será carregado como link morto. Se houver perfil real, informar a URL.
- **Arquivamento do WordPress** (dump do banco + `wp-content/uploads`) entra na lista de agosto, com os demais itens do cancelamento do plano M.
