# Plano de ModernizaÃ§Ã£o do MÃ³dulo de Vendas & IntegraÃ§Ã£o com a Receita

## VisÃ£o Geral e DiagnÃ³stico da Estrutura Atual

Atualmente no Map-OS, o mÃ³dulo de Vendas compartilha a mesma estrutura de status e conceitos das **Ordens de ServiÃ§o (OS)** de bancada/oficina. Isso gera distorÃ§Ãµes comerciais como:
- **Status incompatÃ­veis**: Uma venda exibe status como *"Aguardando PeÃ§as"* ou *"Em Andamento"*, o que nÃ£o faz sentido no fluxo de comÃ©rcio/PDV.
- **Fluxo comercial confuso**: Dificuldade do vendedor em distinguir o que Ã© um **OrÃ§amento/Proposta**, o que Ã© uma **Venda Aprovada** e o que jÃ¡ foi **ConcluÃ­do e Faturado no Financeiro (Receita)**.

### Diretriz Principal
> **RestriÃ§Ã£o de Banco de Dados**: Este plano utiliza **estritamente os campos jÃ¡ existentes** no banco de dados MySQL (`vendas`, `itens_de_vendas`, `lancamentos`), garantindo 100% de compatibilidade e **zero alteraÃ§Ã£o de tabelas ou SQL DDL**.

---

## Estrutura do Banco e RelaÃ§Ã£o com a Receita

| Campo Existente | Tabela | FunÃ§Ã£o no Novo Fluxo Comercial |
| :--- | :--- | :--- |
| `status` | `vendas` | Pipeline Comercial: `OrÃ§amento` âž” `Aprovado` âž” `Faturado` âž” `Cancelado` |
| `faturado` | `vendas` | `0` = Pendente / Proposta \| `1` = ConcluÃ­do & Integrado Ã  Receita |
| `valor_desconto` | `vendas` | Valor final da venda a ser computado na Receita |
| `lancamentos_id` | `vendas` | ID do registro financeiro gerado em `lancamentos` |
| `vendas_id` | `lancamentos` | VÃ­nculo bidirecional da receita com o pedido de venda |
| `tipo` | `lancamentos` | Fixado como `'receita'` |
| `baixado` | `lancamentos` | `1` = Dinheiro/Pix no Caixa (Recebido) \| `0` = Contas a Receber (A prazo) |

---

## Proposed Changes

### 1. Novo Pipeline Comercial de Vendas (Status Exclusivos)

Eliminaremos a confusÃ£o com OS redefinindo o fluxo de vendas para 4 estÃ¡gios comerciais claros:

1. **OrÃ§amento / Proposta** (`status = 'OrÃ§amento'`)
   - NegociaÃ§Ã£o inicial com o cliente.
   - **NÃ£o baixa estoque** e **nÃ£o gera receita**.
2. **Venda Aprovada** (`status = 'Aprovado'`)
   - Pedido confirmado pelo cliente, aguardando faturamento/pagamento.
   - **Reserva/baixa de estoque** opcional do pedido.
3. **Venda Faturada / ConcluÃ­da** (`status = 'Faturado'`, `faturado = 1`)
   - Venda finalizada com sucesso.
   - **Gera a Receita** na tabela `lancamentos` automaticamente.
   - **Baixa de estoque** garantida dos produtos.
4. **Venda Cancelada** (`status = 'Cancelado'`)
   - Venda cancelada ou estornada.
   - **Devolve os produtos ao estoque** e **cancela/exclui o lanÃ§amento de receita** no financeiro se jÃ¡ tinha sido faturada.

---

### 2. IntegraÃ§Ã£o com a Receita e Caixa (LanÃ§amentos)

#### Faturamento AutomÃ¡tico (PDV & Venda Direta)
Ao faturar uma venda (`faturado = 1`), o controller [`Vendas.php`](file:///c:/laragon/www/mapos/application/controllers/Vendas.php) gera o registro de receita com os seguintes campos existentes:
- `descricao`: `"Fatura de Venda #ID - [Nome do Cliente]"`
- `valor`: `$venda->valor_desconto`
- `data_vencimento`: Data informada no faturamento
- `data_pagamento`: Data do recebimento (se baixado)
- `baixado`: `1` se pago na hora (Caixa) ou `0` se a prazo (Contas a Receber)
- `cliente_fornecedor`: Nome do cliente
- `forma_pgto`: Dinheiro, Pix, CartÃ£o de CrÃ©dito, CartÃ£o de DÃ©bito, Boleto, etc.
- `tipo`: `'receita'`
- `vendas_id`: ID da Venda

---

### 3. Ajustes em Telas e Interface (UX Moderno)

#### [MODIFY] [vendas.php](file:///c:/laragon/www/mapos/application/views/vendas/vendas.php)
- **Status em Massa & Filtro de Status**: Atualizar opÃ§Ãµes do `<select>` para conter apenas os status comerciais vÃ¡lidos: `OrÃ§amento`, `Aprovado`, `Faturado`, `Cancelado`.
- **Badges Modernos**: EstilizaÃ§Ã£o visual distinta para cada etapa comercial (ex: *Laranja* para OrÃ§amento, *Azul* para Aprovado, *Verde/Roxo* para Faturado, *Vermelho* para Cancelado).

#### [MODIFY] [adicionarVenda.php](file:///c:/laragon/www/mapos/application/views/vendas/adicionarVenda.php) & [editarVenda.php](file:///c:/laragon/www/mapos/application/views/vendas/editarVenda.php)
- **Dropdown de Status Comercial**: Restringir seleÃ§Ã£o aos status de Vendas (remover "Aguardando PeÃ§as", "Em Andamento", etc.).
- **Painel Resumo financeiro da Venda**: ExibiÃ§Ã£o clara do Subtotal, Desconto e Total LÃ­quido a Faturar.

#### [MODIFY] [Vendas.php](file:///c:/laragon/www/mapos/application/controllers/Vendas.php) & [Vendas_model.php](file:///c:/laragon/www/mapos/application/models/Vendas_model.php)
- Ajustar mÃ©todos de faturamento, alteraÃ§Ã£o de status em massa e cancelamento para respeitarem as regras de estoque e de receita.

---

## Verification Plan

### Automated Tests / Regras de NegÃ³cio
1. **Teste de Faturamento e Receita**:
   - Faturar uma venda no status *Aprovado* âž” Verificar se o lanÃ§amento com `tipo = 'receita'` foi inserido na tabela `lancamentos` vinculado a `vendas_id`.
2. **Teste de Cancelamento**:
   - Cancelar uma venda faturada âž” Verificar se a receita associada em `lancamentos` Ã© removida/estornada e o estoque dos produtos devolvido.
3. **Teste de Filtros e Badges**:
   - Acessar a grid de Vendas âž” Confirmar que apenas status comerciais de vendas sÃ£o listados nos seletores e nos badges.

### Manual Verification
- Testar a criaÃ§Ã£o de Venda do estado de *OrÃ§amento* atÃ© o *Faturamento* via navegador.
- Verificar o lanÃ§amento gerado no menu **Financeiro âž” LanÃ§amentos** garantindo que o valor e cliente estÃ£o corretos.

