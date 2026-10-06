<?php if (empty($parcial)) { ?>
<!DOCTYPE html>
<html>

<head>
	<title>Etiquetas</title>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/mpdf-barcode.css" />

</head>

<body style="background-color: transparent">

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<div class="widget-box">
					<div class="widget-content nopadding tab-content">
						<?php } ?>

					<?php
                        $formato = (string) $this->input->get('etiquetaCode');
$porEstoque = isset($porEstoque) ? (bool) $porEstoque : isset($_GET['qtdEtiqueta']);
$emLinha = in_array($formato, ['EAN13', 'UPCA', 'QR'], true);
$classeEtiqueta = $emLinha ? 'detalheProdutoEtiquetaEan13' : 'detalheProdutoEtiqueta';
$tamanhoBarcode = $emLinha ? '0.62' : '0.7';

if (! isset($itens)) {
    $itens = [];
    foreach ($produtos as $produto) {
        $quantidade = $porEstoque ? max(0, (int) $produto->estoque) : 1;
        if ($quantidade > 0) {
            $itens[] = ['produto' => $produto, 'quantidade' => $quantidade];
        }
    }
}

foreach ($itens as $item) {
    $produto = $item['produto'];
    $string = strtoupper($produto->descricao);
    $precoVenda = str_replace('.', ',', $produto->precoVenda);
    $codigoValido = barcode_valido($produto->codDeBarra, $formato);
    for ($i = 0; $i < $item['quantidade']; $i++) {
        ?>
							<div class="<?= $classeEtiqueta ?>">
								<div class="descricaoProdutoEtiqueta">
									<div>
										<strong>
											<?php print(limitarTexto($string, $limite = 23)); ?>
										</strong>
									</div>
								</div>
								<div class="textoProdutoEtiqueta">Cod:
									<b>
										<?php echo $produto->idProdutos; ?>
									</b>
									<br /> Preço: R$
									<b>
										<?php echo $precoVenda; ?>
									</b>
								</div>
								<div class="barcodecell">
									<?php if ($codigoValido) { ?>
										<barcode code="<?= $produto->codDeBarra ?>" text="0" type="<?= $formato ?>" size="<?= $tamanhoBarcode ?>" disableborder="0"
											class="barcode" />
									<?php } else { ?>
										<div class="codigo-sem-barra"><?= html_escape((string) $produto->codDeBarra) ?: 'Sem código de barras' ?></div>
									<?php } ?>
								</div>
							</div>
							<?php
    }
}
?>

					<?php if (empty($parcial)) { ?>
					</div>
				</div>
			</div>
		</div>
	</div>

</body>

</html>
					<?php } ?>
