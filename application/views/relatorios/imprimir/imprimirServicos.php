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
                        <table class="rel-table">
                            <colgroup>
                                <col style="width: 25%" />
                                <col style="width: 55%" />
                                <col style="width: 20%" />
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Preço</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($servicos)) : ?>
                                    <tr>
                                        <td colspan="3" class="rel-vazio">Nenhum serviço no período informado.</td>
                                    </tr>
                                <?php endif ?>
                                <?php foreach ($servicos as $s) : ?>
                                    <tr>
                                        <td><?= html_escape((string) $s->nome) ?: '-' ?></td>
                                        <td><?= html_escape((string) $s->descricao) ?: '-' ?></td>
                                        <td class="num">R$ <?= number_format((float) $s->preco, 2, ',', '.') ?></td>
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
