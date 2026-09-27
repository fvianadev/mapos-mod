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
                                <col style="width: 16%" />
                                <col style="width: 16%" />
                                <col style="width: 24%" />
                                <col style="width: 14%" />
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Documento</th>
                                    <th>Telefone</th>
                                    <th>Email</th>
                                    <th>Cadastro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($clientes)) : ?>
                                    <tr>
                                        <td colspan="5" class="rel-vazio">Nenhum cliente no período informado.</td>
                                    </tr>
                                <?php endif ?>
                                <?php foreach ($clientes as $c) :
                                    $tsCadastro = strtotime((string) $c->dataCadastro);
                                    ?>
                                    <tr>
                                        <td><?= html_escape((string) $c->nomeCliente) ?: '-' ?></td>
                                        <td class="ctr"><?= html_escape((string) $c->documento) ?: '-' ?></td>
                                        <td class="ctr"><?= html_escape((string) $c->telefone) ?: '-' ?></td>
                                        <td class="ctr"><?= html_escape((string) $c->email) ?: '-' ?></td>
                                        <td class="ctr"><?= $tsCadastro ? date('d/m/Y', $tsCadastro) : '-' ?></td>
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
