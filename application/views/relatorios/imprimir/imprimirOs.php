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
                                <col style="width: 5%" />
                                <col style="width: 16%" />
                                <col style="width: 8%" />
                                <col style="width: 8%" />
                                <col style="width: 24%" />
                                <col style="width: 9%" />
                                <col style="width: 9%" />
                                <col style="width: 8%" />
                                <col style="width: 6%" />
                                <col style="width: 7%" />
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>OS</th>
                                    <th>CLIENTE</th>
                                    <th>STATUS</th>
                                    <th>DATA</th>
                                    <th>DESCRIÇÃO</th>
                                    <th>TOTAL PRODUTOS</th>
                                    <th>TOTAL SERVIÇOS</th>
                                    <th>TOTAL</th>
                                    <th>DESCONTO</th>
                                    <th>TOTAL COM DESCONTO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($os)) : ?>
                                    <tr>
                                        <td colspan="10" class="rel-vazio">Nenhuma ordem de serviço no período informado.</td>
                                    </tr>
                                <?php endif ?>
                                <?php foreach ($os as $c) :
                                    $tsData = strtotime((string) $c->dataInicial);
                                    ?>
                                    <tr>
                                        <td class="ctr"><?= (int) $c->idOs ?></td>
                                        <td><?= html_escape((string) $c->nomeCliente) ?: '-' ?></td>
                                        <td class="ctr"><?= html_escape((string) $c->status) ?: '-' ?></td>
                                        <td class="ctr"><?= $tsData ? date('d/m/Y', $tsData) : '-' ?></td>
                                        <?php
                                        // descricaoProduto vem do editor rich-text (trumbowyg) com <p>/<ul>;
                                        // sem tratar, o mPDF imprimiria as tags literalmente.
                                        $descricao = html_entity_decode((string) $c->descricaoProduto, ENT_QUOTES, 'UTF-8');
                                    $descricao = preg_replace('/<\s*br\s*\/?\s*>/i', ' ', $descricao);
                                    $descricao = preg_replace('/<\/\s*(p|li|div|tr|h[1-6])\s*>/i', ' ', $descricao);
                                    $descricao = trim(preg_replace('/\s+/u', ' ', strip_tags($descricao)));
                                    ?>
                                        <td><?= html_escape($descricao) ?: '-' ?></td>
                                        <td class="num">R$ <?= number_format((float) $c->total_produto, 2, ',', '.') ?></td>
                                        <td class="num">R$ <?= number_format((float) $c->total_servico, 2, ',', '.') ?></td>
                                        <td class="num">R$ <?= number_format((float) $c->total_produto + (float) $c->total_servico, 2, ',', '.') ?></td>
                                        <td class="num"><?= ($c->tipo_desconto == 'real' ? 'R$ ' : '') . number_format((float) $c->desconto, 2, ',', '.') . ($c->tipo_desconto == 'porcento' ? ' %' : '') ?></td>
                                        <td class="num">R$ <?= number_format($c->valor_desconto != 0 ? (float) $c->valor_desconto : (float) $c->total_produto + (float) $c->total_servico, 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                            <tfoot>
                                <tr class="rel-total">
                                    <td colspan="5"></td>
                                    <td class="num">R$ <?= number_format((float) $total_produtos, 2, ',', '.') ?></td>
                                    <td class="num">R$ <?= number_format((float) $total_servicos, 2, ',', '.') ?></td>
                                    <td class="num">R$ <?= number_format((float) $total_produtos + (float) $total_servicos, 2, ',', '.') ?></td>
                                    <td class="num"></td>
                                    <td class="num">R$ <?= number_format((float) $total_geral, 2, ',', '.') ?></td>
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
