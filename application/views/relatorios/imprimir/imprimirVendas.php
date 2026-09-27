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
                        <h4 class="rel-titulo"><?= html_escape(ucfirst($title)) ?></h4>
                    </div>
                    <div class="widget_content nopadding">
                        <?php
                        $sum = 0;
                        foreach ($vendas as $v) {
                            $sum += $v->valor_desconto != 0 ? $v->valor_desconto : $v->valorTotal;
                        }
                        ?>
                        <table class="rel-table">
                            <colgroup>
                                <col style="width: 6%" />
                                <col style="width: 34%" />
                                <col style="width: 13%" />
                                <col style="width: 9%" />
                                <col style="width: 10%" />
                                <col style="width: 10%" />
                                <col style="width: 10%" />
                                <col style="width: 8%" />
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Total</th>
                                    <th>Desconto</th>
                                    <th>Total Com Desconto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($vendas)) : ?>
                                    <tr>
                                        <td colspan="8" class="rel-vazio">Nenhuma venda no período informado.</td>
                                    </tr>
                                <?php endif ?>
                                <?php foreach ($vendas as $v) :
                                    $tsData = strtotime((string) $v->dataVenda);
                                    ?>
                                    <tr>
                                        <td class="ctr"><?= (int) $v->idVendas ?></td>
                                        <td><?= html_escape((string) $v->nomeCliente) ?: '-' ?></td>
                                        <td class="ctr"><?= html_escape((string) $v->nome) ?: '-' ?></td>
                                        <td class="ctr"><?= html_escape((string) $v->status) ?: '-' ?></td>
                                        <td class="ctr"><?= $tsData ? date('d/m/Y', $tsData) : '-' ?></td>
                                        <td class="num">R$ <?= number_format((float) $v->valorTotal, 2, ',', '.') ?></td>
                                        <td class="num"><?= ($v->tipo_desconto == 'real' ? 'R$ ' : '') . number_format((float) $v->desconto, 2, ',', '.') . ($v->tipo_desconto == 'porcento' ? ' %' : '') ?></td>
                                        <td class="num">R$ <?= number_format($v->valor_desconto != 0 ? (float) $v->valor_desconto : (float) $v->valorTotal, 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                            <tfoot>
                                <tr class="rel-total">
                                    <td colspan="6"></td>
                                    <td class="num">TOTAL:</td>
                                    <td class="num">R$ <?= number_format($sum, 2, ',', '.') ?></td>
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
