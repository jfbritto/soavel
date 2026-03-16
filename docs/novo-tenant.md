# Novo Tenant — Passo a Passo

Guia para provisionar um novo cliente no sistema HelpFlux Veiculos.

> **Exemplo:** dominio `friedrichveiculos.com.br`

---

## 1. Criar dominio no cPanel (Hostgator)

1. Acessar cPanel > **Dominios** > Criar um Novo Dominio
2. Dominio: `friedrichveiculos.com.br` (sem `www`)
3. Document Root: deixar o padrao (`friedrichveiculos.com.br`)
4. Clicar em **Enviar**

> O subdominio `.helpdiet.com.br` eh criado automaticamente pelo cPanel. Ignorar.

---

## 2. Copiar o Soavel (template base)

```bash
rm -rf ~/friedrichveiculos.com.br/*
rm -rf ~/friedrichveiculos.com.br/.* 2>/dev/null
cp -r ~/soavelveiculos.com.br/. ~/friedrichveiculos.com.br/
```

---

## 3. Reconectar o Git

```bash
cd ~/friedrichveiculos.com.br
rm -rf .git
git clone https://github.com/jfbritto/soavel.git .git --bare
git checkout -f main
```

> Todos os tenants usam o mesmo repositorio. Um `git pull` atualiza todos.

---

## 4. Criar banco de dados no cPanel

1. cPanel > **MySQL Databases**
2. Criar banco: `helpdi71_NOME` (ex: `helpdi71_friedrich`)
3. Criar usuario: `helpdi71_NOME` (mesma convencao)
4. Associar usuario ao banco com **ALL PRIVILEGES**

---

## 5. Editar .env

```bash
nano ~/friedrichveiculos.com.br/.env
```

Ajustar:

```env
APP_NAME="Friedrich Veiculos"
APP_URL=https://friedrichveiculos.com.br
DB_DATABASE=helpdi71_friedrich
DB_USERNAME=helpdi71_friedrich
DB_PASSWORD=SENHA_CRIADA
```

> Manter `APP_ENV=production` e `APP_DEBUG=false`.

---

## 6. Gerar nova APP_KEY

```bash
cd ~/friedrichveiculos.com.br
php artisan key:generate --force
```

---

## 7. Rodar migrations e seed

```bash
php artisan migrate --force
php artisan db:seed --force
```

---

## 8. Recriar storage link

```bash
rm -f public/storage
php artisan storage:link
```

---

## 9. Limpar caches herdados do Soavel

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 10. Testar

Acessar `https://friedrichveiculos.com.br`

> DNS pode levar de 10 min a 24h para propagar.

---

## 11. Cadastrar no Master

Em `veiculos.helpflux.com.br/tenants` > **Novo Tenant**:

- **Nome:** Friedrich Veiculos
- **Dominio:** `https://friedrichveiculos.com.br`
- **Mensalidade:** valor combinado
- **Dados do proprietario:** nome, CPF/CNPJ, email, telefone

Depois de cadastrar:
- O **API Token** sera gerado automaticamente
- Ativar **cobranca** quando desejar

---

## Atualizacao futura (todos os tenants)

Para aplicar atualizacoes do Soavel em todos os clientes:

```bash
cd ~/soavelveiculos.com.br && git pull
cd ~/friedrichveiculos.com.br && git pull
# repetir para cada tenant...
```

---

## Checklist resumido

- [ ] Dominio criado no cPanel
- [ ] Arquivos copiados do Soavel
- [ ] Git reconectado ao repositorio
- [ ] Banco de dados criado e associado
- [ ] `.env` ajustado (APP_NAME, APP_URL, DB_*)
- [ ] APP_KEY gerada
- [ ] Migrations e seed rodados
- [ ] Storage link criado
- [ ] Caches limpos
- [ ] Site acessivel no navegador
- [ ] Tenant cadastrado no Master
