<!DOCTYPE html>
<html>

<head>
    <title>MAPOS</title>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php require __DIR__ . '/_estilosRelatorio.php'; ?>
</head>

<body style="background-color: transparent">

    <div class="container-fluid">

        <div class="row-fluid">
            <div class="span12">

                <div class="widget-box">
                    <?= $topo ?>

                    <div class="widget-title">
                        <h4 class="rel-titulo">Relatório Financeiro</h4>
                    </div>
                    <div class="widget-content nopadding tab-content">

                        <table class="rel-table">
                            <colgroup>
                                <col style="width: 18%" />
                                <col style="width: 7%" />
                                <col style="width: 10%" />
                                <col style="width: 10%" />
                                <col style="width: 11%" />
                                <col style="width: 11%" />
                                <col style="width: 11%" />
                                <col style="width: 12%" />
                                <col style="width: 10%" />
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Cliente/Fornecedor</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Desconto</th>
                                    <th>Valor Total</th>
                                    <th>Vencimento</th>
                                    <th>Pagamento</th>
                                    <th>Forma de Pgto.</th>
                                    <th>Situação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $totalReceita = 0;
    $totalDespesa = 0;

    if (empty($lancamentos)) {
        echo '<tr><td colspan="9" class="rel-vazio">Nenhum lançamento no período informado.</td></tr>';
    }

    foreach ($lancamentos as $l) {
        $tsVencimento = strtotime((string) $l->data_vencimento);
        $tsPagamento = strtotime((string) $l->data_pagamento);
        $vencimento = $tsVencimento ? date('d/m/Y', $tsVencimento) : '-';
        $pagamento = $tsPagamento ? date('d/m/Y', $tsPagamento) : '-';

        $situacao = $l->baixado == 1 ? 'Pago' : 'Pendente';

        if ($l->tipo == 'receita') {
            $totalReceita += $l->valor_desconto != 0 ? $l->valor_desconto : $l->valor;
        } else {
            $totalDespesa += $l->valor_desconto != 0 ? $l->valor_desconto : $l->valor;
        }

        $valorLiquido = $l->valor_desconto != 0 ? $l->valor_desconto : $l->valor;
        ?>
                                <tr>
                                    <td><?php echo html_escape((string) $l->cliente_fornecedor) ?: '-' ?></td>
                                    <td class="ctr"><?php echo html_escape(ucfirst((string) $l->tipo)) ?></td>
                                    <td class="num">R$ <?php echo number_format($l->valor, 2, ',', '.') ?></td>
                                    <td class="num"><?php echo ($l->tipo_desconto == 'real' ? 'R$ ' : '') . number_format($l->desconto, 2, ',', '.') . ($l->tipo_desconto == 'porcento' ? ' %' : '') ?></td>
                                    <td class="num">R$ <?php echo number_format($valorLiquido, 2, ',', '.') ?></td>
                                    <td class="ctr"><?php echo $vencimento ?></td>
                                    <td class="ctr"><?php echo $pagamento ?></td>
                                    <td class="ctr"><?php echo html_escape((string) $l->forma_pgto) ?: '-' ?></td>
                                    <td class="ctr"><?php echo $situacao ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="rel-verde">Total Receitas:</td>
                                    <td colspan="2" class="rel-verde">R$ <?php echo number_format($totalReceita, 2, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="rel-vermelho">Total Despesas:</td>
                                    <td colspan="2" class="rel-vermelho">R$ <?php echo number_format($totalDespesa, 2, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td colspan="7">Saldo:</td>
                                    <td colspan="2">R$ <?php echo number_format($totalReceita - $totalDespesa, 2, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <h5 class="rel-data">Data do Relatório: <?php echo date('d/m/Y'); ?></h5>

            </div>
        </div>
    </div>
</body>

</html>
