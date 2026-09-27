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
                        <h4 class="rel-titulo"><?= html_escape(isset($title) ? $title : 'Relatório SKU') ?></h4>
                    </div>
                    <div class="widget-content nopadding tab-content">
                        <table class="rel-table">
                            <colgroup>
                                <col style="width: 7%" />
                                <col style="width: 15%" />
                                <col style="width: 7%" />
                                <col style="width: 20%" />
                                <col style="width: 7%" />
                                <col style="width: 8%" />
                                <col style="width: 9%" />
                                <col style="width: 9%" />
                                <col style="width: 9%" />
                                <col style="width: 9%" />
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>ID Cliente</th>
                                    <th>Nome Cliente</th>
                                    <th>ID Produto</th>
                                    <th>Descrição Produto</th>
                                    <th>Quantidade</th>
                                    <th>ID Relacionado</th>
                                    <th>Data</th>
                                    <th>Preço Unitário</th>
                                    <th>Preço Total</th>
                                    <th>Origem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($resultados)) : ?>
                                    <tr>
                                        <td colspan="10" class="rel-vazio">Nenhum registro no período informado.</td>
                                    </tr>
                                <?php endif ?>
                                <?php foreach ($resultados as $r) :
                                    $tsData = strtotime((string) $r->dataOcorrencia);
                                    ?>
                                    <tr>
                                        <td class="ctr"><?= (int) $r->idClientes ?></td>
                                        <td><?= html_escape((string) $r->nomeCliente) ?: '-' ?></td>
                                        <td class="ctr"><?= (int) $r->idProdutos ?></td>
                                        <td><?= html_escape((string) $r->descricao) ?: '-' ?></td>
                                        <td class="ctr"><?= html_escape((string) $r->quantidade) ?></td>
                                        <td class="ctr"><?= (int) $r->idRelacionado ?></td>
                                        <td class="ctr"><?= $tsData ? date('d/m/Y', $tsData) : '-' ?></td>
                                        <td class="num">R$ <?= number_format((float) $r->preco, 2, ',', '.') ?></td>
                                        <td class="num">R$ <?= number_format((float) $r->precoTotal, 2, ',', '.') ?></td>
                                        <td class="ctr"><?= html_escape((string) $r->origem) ?: '-' ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <h5 class="rel-data">Data do Relatório: <?php echo date('d/m/Y'); ?></h5>
            </div>
        </div>
    </div>
</body>

</html>
