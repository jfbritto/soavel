# Venda com múltiplos veículos de troca

Data: 2026-09-25

## Problema

Um cliente fechou uma venda entregando **dois veículos** mais dinheiro. O sistema só aceita
um veículo de troca por venda: a tabela `sales` tem as colunas `troca_vehicle_id` e
`valor_troca`, o formulário tem um único bloco `troca_*` e a tela da venda mostra um único
veículo de troca.

## Como funciona hoje (código como fonte da verdade)

- `sales.troca_vehicle_id` (FK nullable para `vehicles`) e `sales.valor_troca`.
- `Sale::trocaVehicle()` (belongsTo) e `Vehicle::vendaOrigem()` (hasOne por `troca_vehicle_id`).
- `SaleController::store` valida os campos `troca_*` só quando `tipo_pagamento` é
  `permuta` ou `misto` **e** `troca_marca` foi preenchido. Cria o veículo no estoque como
  `disponivel` com `preco` e `preco_compra` iguais ao valor avaliado e grava o id na venda.
- `sales/show` exibe o bloco "Veículo de Troca"; `vehicles/show` exibe o alerta
  "Veículo entrou como troca na Venda #N".

## Decisão

Criar a tabela de ligação `sale_troca_vehicles` (`sale_id`, `vehicle_id`, `valor_troca`),
seguindo o padrão já usado em `vehicle_partners` (belongsToMany com `withPivot`).

- Uma venda pode ter N veículos de troca; cada um com seu valor avaliado.
- `Sale::trocaVehicles()` (belongsToMany) substitui `trocaVehicle()`.
- `Sale::valor_troca_total` (accessor) soma os valores avaliados.
- `Vehicle::vendasOrigem()` (belongsToMany) + accessor `vendaOrigem` que devolve a primeira,
  mantendo a tela do veículo quase igual.
- Formulário: bloco de troca vira repetível ("Adicionar outro veículo"), com nomes
  `trocas[i][campo]`. Blocos totalmente vazios são descartados antes da validação.
- Validação passa para `StoreSaleRequest` (regras `trocas.*.campo`), saindo do controller.
- Criação da venda e dos veículos de troca dentro de uma transação.

### Compatibilidade

- A migration copia os dados existentes de `troca_vehicle_id`/`valor_troca` para a nova tabela.
- As colunas antigas **não são removidas** nesta entrega: o projeto não tem `doctrine/dbal`
  (Laravel 8) e os testes rodam em SQLite, onde `dropColumn` falharia. Elas deixam de ser
  lidas e escritas pelo código. Podem ser removidas numa migration futura.
- Sem regra de unicidade em `vehicle_id` na nova tabela (dados legados poderiam violar);
  unicidade só do par `(sale_id, vehicle_id)`.

## Fora de escopo

- Editar uma venda depois de criada (não existe hoje).
- Selecionar um veículo já cadastrado no estoque como troca (hoje sempre cria um novo).
