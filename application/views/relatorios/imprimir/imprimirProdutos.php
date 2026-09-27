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
                    <div class="widget-content nopadding tab-content">
                        <table class="rel-table">
                            <colgroup>
                                <col style="width: 30%" />
                                <col style="width: 6%" />
                                <col style="width: 12%" />
                                <col style="width: 12%" />
                                <col style="width: 10%" />
                                <col style="width: 15%" />
                                <col style="width: 15%" />
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>UN</th>
                                    <th>Preço Compra</th>
                                    <th>Preço Venda</th>
                                    <th>Estoque</th>
                                    <th>Valor (Venda)</th>
                                    <th>Valor (Compra)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($produtos)) : ?>
                                    <tr>
                                        <td colspan="7" class="rel-vazio">Nenhum produto no período informado.</td>
                                    </tr>
                                <?php endif ?>
                                <?php foreach ($produtos as $p) : ?>
                                    <tr>
                                        <td><?= html_escape((string) $p->descricao) ?: '-' ?></td>
                                        <td class="ctr"><?= html_escape((string) $p->unidade) ?: '-' ?></td>
                                        <td class="num">R$ <?= number_format((float) $p->precoCompra, 2, ',', '.') ?></td>
                                        <td class="num">R$ <?= number_format((float) $p->precoVenda, 2, ',', '.') ?></td>
                                        <td class="ctr"><?= html_escape((string) $p->estoque) ?></td>
                                        <td class="num">R$ <?= number_format((float) $p->valorEstoque, 2, ',', '.') ?></td>
                                        <td class="num">R$ <?= number_format(((float) $p->estoque) * ((float) $p->precoCompra), 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="ctr">Itens em Estoque</td>
                                    <td class="ctr">Valor do Estoque (Venda)</td>
                                    <td class="ctr">Valor do Estoque (Compra)</td>
                                </tr>
                                <tr class="rel-total">
                                    <td colspan="4"></td>
                                    <td class="ctr"><?= array_sum(array_column($produtos, 'estoque')) ?></td>
                                    <td class="num">R$ <?= number_format(array_sum(array_column($produtos, 'valorEstoque')), 2, ',', '.') ?></td>
                                    <td class="num">R$ <?= number_format(array_sum(array_column($produtos, 'valorEstoqueR')), 2, ',', '.') ?></td>
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
