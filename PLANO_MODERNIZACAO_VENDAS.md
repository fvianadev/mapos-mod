# Plano de Modernização do Módulo de Vendas & Integração com a Receita

## Visão Geral e Diagnóstico da Estrutura Atual

Atualmente no Map-OS, o módulo de Vendas compartilha a mesma estrutura de status e conceitos das **Ordens de Serviço (OS)** de bancada/oficina. Isso gera distorções comerciais como:
- **Status incompatíveis**: Uma venda exibe status como *"Aguardando Peças"* ou *"Em Andamento"*, o que não faz sentido no fluxo de comércio/PDV.
- **Fluxo comercial confuso**: Dificuldade do vendedor em distinguir o que é um **Orçamento/Proposta**, o que é uma **Venda Aprovada** e o que já foi **Concluído e Faturado no Financeiro (Receita)**.

### Diretriz Principal
> **Restrição de Banco de Dados**: Este plano utiliza **estritamente os campos já existentes** no banco de dados MySQL (`vendas`, `itens_de_vendas`, `lancamentos`), garantindo 100% de compatibilidade e **zero alteração de tabelas ou SQL DDL**.

---

## Estrutura do Banco e Relação com a Receita

| Campo Existente | Tabela | Função no Novo Fluxo Comercial |
| :--- | :--- | :--- |
| `status` | `vendas` | Pipeline Comercial: `Orçamento` ➔ `Aprovado` ➔ `Faturado` ➔ `Cancelado` |
| `faturado` | `vendas` | `0` = Pendente / Proposta \| `1` = Concluído & Integrado à Receita |
| `valor_desconto` | `vendas` | Valor final da venda a ser computado na Receita |
| `lancamentos_id` | `vendas` | ID do registro financeiro gerado em `lancamentos` |
| `vendas_id` | `lancamentos` | Vínculo bidirecional da receita com o pedido de venda |
| `tipo` | `lancamentos` | Fixado como `'receita'` |
| `baixado` | `lancamentos` | `1` = Dinheiro/Pix no Caixa (Recebido) \| `0` = Contas a Receber (A prazo) |

---

## Proposed Changes

### 1. Novo Pipeline Comercial de Vendas (Status Exclusivos)

Eliminaremos a confusão com OS redefinindo o fluxo de vendas para 4 estágios comerciais claros:

1. **Orçamento / Proposta** (`status = 'Orçamento'`)
   - Negociação inicial com o cliente.
   - **Não baixa estoque** e **não gera receita**.
2. **Venda Aprovada** (`status = 'Aprovado'`)
   - Pedido confirmado pelo cliente, aguardando faturamento/pagamento.
   - **Reserva/baixa de estoque** opcional do pedido.
3. **Venda Faturada / Concluída** (`status = 'Faturado'`, `faturado = 1`)
   - Venda finalizada com sucesso.
   - **Gera a Receita** na tabela `lancamentos` automaticamente.
   - **Baixa de estoque** garantida dos produtos.
4. **Venda Cancelada** (`status = 'Cancelado'`)
   - Venda cancelada ou estornada.
   - **Devolve os produtos ao estoque** e **cancela/exclui o lançamento de receita** no financeiro se já tinha sido faturada.

---

### 2. Integração com a Receita e Caixa (Lançamentos)

#### Faturamento Automático (PDV & Venda Direta)
Ao faturar uma venda (`faturado = 1`), o controller [`Vendas.php`](file:///c:/laragon/www/mapos/application/controllers/Vendas.php) gera o registro de receita com os seguintes campos existentes:
- `descricao`: `"Fatura de Venda #ID - [Nome do Cliente]"`
- `valor`: `$venda->valor_desconto`
- `data_vencimento`: Data informada no faturamento
- `data_pagamento`: Data do recebimento (se baixado)
- `baixado`: `1` se pago na hora (Caixa) ou `0` se a prazo (Contas a Receber)
- `cliente_fornecedor`: Nome do cliente
- `forma_pgto`: Dinheiro, Pix, Cartão de Crédito, Cartão de Débito, Boleto, etc.
- `tipo`: `'receita'`
- `vendas_id`: ID da Venda

---

### 3. Ajustes em Telas e Interface (UX Moderno)

#### [MODIFY] [vendas.php](file:///c:/laragon/www/mapos/application/views/vendas/vendas.php)
- **Status em Massa & Filtro de Status**: Atualizar opções do `<select>` para conter apenas os status comerciais válidos: `Orçamento`, `Aprovado`, `Faturado`, `Cancelado`.
- **Badges Modernos**: Estilização visual distinta para cada etapa comercial (ex: *Laranja* para Orçamento, *Azul* para Aprovado, *Verde/Roxo* para Faturado, *Vermelho* para Cancelado).

#### [MODIFY] [adicionarVenda.php](file:///c:/laragon/www/mapos/application/views/vendas/adicionarVenda.php) & [editarVenda.php](file:///c:/laragon/www/mapos/application/views/vendas/editarVenda.php)
- **Dropdown de Status Comercial**: Restringir seleção aos status de Vendas (remover "Aguardando Peças", "Em Andamento", etc.).
- **Painel Resumo financeiro da Venda**: Exibição clara do Subtotal, Desconto e Total Líquido a Faturar.

#### [MODIFY] [Vendas.php](file:///c:/laragon/www/mapos/application/controllers/Vendas.php) & [Vendas_model.php](file:///c:/laragon/www/mapos/application/models/Vendas_model.php)
- Ajustar métodos de faturamento, alteração de status em massa e cancelamento para respeitarem as regras de estoque e de receita.

---

## Verification Plan

### Automated Tests / Regras de Negócio
1. **Teste de Faturamento e Receita**:
   - Faturar uma venda no status *Aprovado* ➔ Verificar se o lançamento com `tipo = 'receita'` foi inserido na tabela `lancamentos` vinculado a `vendas_id`.
2. **Teste de Cancelamento**:
   - Cancelar uma venda faturada ➔ Verificar se a receita associada em `lancamentos` é removida/estornada e o estoque dos produtos devolvido.
3. **Teste de Filtros e Badges**:
   - Acessar a grid de Vendas ➔ Confirmar que apenas status comerciais de vendas são listados nos seletores e nos badges.

### Manual Verification
- Testar a criação de Venda do estado de *Orçamento* até o *Faturamento* via navegador.
- Verificar o lançamento gerado no menu **Financeiro ➔ Lançamentos** garantindo que o valor e cliente estão corretos.

