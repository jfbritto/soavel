# Cliente pessoa jurídica (CNPJ)

Data: 2026-10-02

## Problema

Um cliente da loja é uma empresa, e o cadastro de clientes só tinha CPF: coluna `cpf`
única, máscara de CPF no formulário e no modal rápido da venda, e verificação de
duplicado por CPF.

## Como era (código como fonte da verdade)

- `customers.cpf` string(14) unique nullable. Sem coluna de tipo.
- `StoreCustomerRequest`: `cpf` nullable, único. Nenhuma validação de dígitos.
- `CustomerController::cpfCheck` (`?cpf=`) e `quickStore` (modal da venda). O quickStore
  não validava CPF duplicado: dependia da checagem do modal; duplicado daria erro 500 pela
  constraint do banco.
- Views: `_form`, `index`, `show` e o modal em `sales/create` tratam só CPF.

## Decisão

- Novas colunas em `customers`: `tipo_pessoa` (`pf`|`pj`, default `pf`) e `cnpj`
  string(18) unique nullable. Clientes existentes viram `pf` sem alteração.
- Model: constante `TIPOS_PESSOA`, default `tipo_pessoa = pf`, accessors `is_pj`,
  `tipo_pessoa_label`, `documento` (CNPJ ou CPF conforme o tipo) e `documento_label`.
- Validação (`StoreCustomerRequest` e `quickStore`): sem `tipo_pessoa` assume `pf`,
  compatível com envios antigos. O documento do outro tipo é descartado (PJ guarda só
  CNPJ, PF só CPF), inclusive ao trocar o tipo na edição. CNPJ único como o CPF.
  Mantida a paridade com o CPF: nenhum dos dois é obrigatório e não há validação de
  dígitos verificadores.
- `quickStore` passa a validar CPF/CNPJ duplicado e devolve 422 com `errors`, que o modal
  já exibe.
- `cpfCheck` mantém a rota `customers.cpf-check` e passa a aceitar `?cnpj=` também.
- Formulário de cliente e modal da venda: rádio PF/PJ. PJ troca o rótulo do nome para
  "Razão Social / Nome da Empresa", esconde CPF e mostra CNPJ com máscara
  `00.000.000/0000-00`. Title case do nome só para PF.
- Lista e perfil mostram "CPF / CNPJ" pelo accessor `documento`, badge "PJ" na lista e
  ícone de prédio no perfil.
- Categorias de documento do cliente ganham "Cartão CNPJ" e "Contrato Social".

## Fora de escopo

- Validar dígitos verificadores de CPF/CNPJ.
- Sócios (`partners`) continuam só com CPF.
