<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<style>
    select {
        width: 70px;
    }
    .bulk-item:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
</style>

<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-cash-register"></i>
        </span>
        <h5>Vendas</h5>
    </div>
    <div class="span12" style="margin-left: 0">
        <form method="get" action="<?php echo base_url(); ?>index.php/vendas/gerenciar">
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aVenda')) { ?>
            <div class="span2">
                <a href="<?php echo base_url(); ?>index.php/vendas/adicionar" class="button btn btn-mini btn-success" style="max-width: 160px">
                    <span class="button__icon"><i class='bx bx-plus-circle'></i></span>
                    <span class="button__text2">Nova Venda</span>
                </a>
            </div>
            <?php } ?>

            <div class="span1">
                <button class="button btn btn-mini btn-warning" style="min-width: 70px">
                    <span class="button__icon"><i class='bx bx-search-alt'></i></span>
                    <span class="button__text2">Filtrar</span>
                </button>
            </div>

            <div class="span2">
                <input type="text" name="pesquisa" id="pesquisa" placeholder="Nome do cliente" class="span12" value="">
            </div>
            <div class="span2">
                <select name="status" class="span12">
                    <option value="">Status</option>
                    <option value="Aberto">Aberto</option>
                    <option value="Faturado">Faturado</option>
                    <option value="Negociação">Negociação</option>
                    <option value="Em Andamento">Em Andamento</option>
                    <option value="Orçamento">Orçamento</option>
                    <option value="Finalizado">Finalizado</option>
                    <option value="Cancelado">Cancelado</option>
                    <option value="Aguardando Peças">Aguard. Peças</option>
                    <option value="Aprovado">Aprovado</option>
                </select>
            </div>
            <div class="span2">
                <input type="date" name="data" id="data" placeholder="De" class="span12 datepicker" autocomplete="off" value="">
            </div>
            <div class="span2">
                <input type="date" name="data2" id="data2" placeholder="Até" class="span12 datepicker" autocomplete="off" value="">
            </div>
        </form>
    </div>

    <div class="row" style="margin: 10px 0 5px">
        <div class="span3">
            <select id="bulk-status" class="span12">
                <option value="">— Status em massa —</option>
                <option value="Aberto">Aberto</option>
                <option value="Em Andamento">Em Andamento</option>
                <option value="Orçamento">Orçamento</option>
                <option value="Negociação">Negociação</option>
                <option value="Aguardando Peças">Aguardando Peças</option>
                <option value="Aprovado">Aprovado</option>
                <option value="Finalizado">Finalizado</option>
                <option value="Faturado">Faturado</option>
                <option value="Cancelado">Cancelado</option>
            </select>
        </div>
        <div class="span2">
            <button id="btn-bulk-status" class="button btn btn-mini btn-primary" disabled style="min-width: 120px">
                <span class="button__icon"><i class='bx bx-check-double'></i></span>
                <span class="button__text2">Aplicar</span>
            </button>
        </div>
    </div>

    <div class="widget-box">
        <div class="widget-content nopadding tab-content">
            <table id="tabela" class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width:20px; text-align:center"><input type="checkbox" id="check-all"></th>
                        <th>Nº</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Data da Venda</th>
                        <th>Venc. da Garantia</th>
                        <th>Valor Total</th>
                        <th>Desconto</th>
                        <th>Valor com Desconto</th>
                        <th>V. T. (Faturado)</th>
                        <th>Status</th>
                        <th>Faturado</th>
                        <th style="text-align:center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if (!$results) {
                            echo '<tr>
                                    <td colspan="13">Nenhuma Venda Cadastrada</td>
                                </tr>';
                        }
        foreach ($results as $r) {
            $dataVenda = date(('d/m/Y'), strtotime($r->dataVenda));
            $vencGarantia = '';
                            
            if ($r->garantia && is_numeric($r->garantia)) {
                $vencGarantia = dateInterval($r->dataVenda, $r->garantia);
            }
            $corGarantia = '';
            if (!empty($vencGarantia)) {
                $dataGarantia = explode('/', $vencGarantia);
                $dataGarantiaFormatada = $dataGarantia[2] . '-' . $dataGarantia[1] . '-' . $dataGarantia[0];
                $corGarantia = (strtotime($dataGarantiaFormatada) >= strtotime(date('d-m-Y'))) ? '#4d9c79' : '#f24c6f';
            } elseif ($r->garantia == "0") {
                $vencGarantia = 'Sem Garantia';
            }

            $faturado = ($r->faturado == 1) ? 'Sim' : 'Não';
            $corStatus = match($r->status) {
                'Aberto' => '#00cd00',
                'Em Andamento' => '#436eee',
                'Orçamento' => '#CDB380',
                'Negociação' => '#AEB404',
                'Cancelado' => '#CD0000',
                'Finalizado' => '#256',
                'Faturado' => '#B266FF',
                'Aguardando Peças' => '#FF7F00',
                'Aprovado' => '#808080',
                default => '#E0E4CC',
            };

            $editavel = $this->vendas_model->isEditable($r->idVendas);

            $checkboxDisabled = $editavel ? '' : ' disabled';
            $titleAttr = $editavel ? '' : ' title="Venda não editável"';

            echo '<tr>';
            echo '<td style="text-align:center; padding:2px"><input type="checkbox" class="bulk-item" value="' . $r->idVendas . '"' . $checkboxDisabled . $titleAttr . '></td>';
            echo '<td>' . $r->idVendas . '</td>';
            echo '<td><a href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '">' . $r->nomeCliente . '</a></td>';
            echo '<td class="ph1">' . $r->nome . '</td>';
            echo '<td>' . $dataVenda . '</td>';
            echo '<td class="ph3"><span class="badge" style="background-color: ' . $corGarantia . '; border-color: ' . $corGarantia . '">' . $vencGarantia . '</span> </td>';

            if ($r->faturado == 1) {
                echo '<td>R$ ' . number_format($r->valorTotal, 2, ',', '.') . '</td>';
                echo '<td>R$ ' . number_format($r->desconto, 2, ',', '.') . '</td>';
                echo '<td>R$ ' . number_format($r->valor_desconto, 2, ',', '.') . '</td>';
                echo '<td>R$ ' . number_format($r->valor_desconto, 2, ',', '.') . '</td>';
            } else {
                $valorProdutos = isset($r->totalProdutos) ? $r->totalProdutos : 0.00;
                $desconto = isset($r->desconto) ? $r->desconto : 0.00;
                $valorComDesconto = $valorProdutos - $desconto;
                            
                echo '<td>R$ ' . number_format($valorProdutos, 2, ',', '.') . '</td>';
                echo '<td>R$ ' . number_format($desconto, 2, ',', '.') . '</td>';
                echo '<td>R$ ' . number_format($valorComDesconto, 2, ',', '.') . '</td>';
                echo '<td>R$ 0,00</td>';
            }

            echo '<td><span class="badge" style="background-color: ' . $corStatus . '; border-color: ' . $corStatus . '">' . $r->status . '</span> </td>';
            echo '<td>' . $faturado . '</td>';
            echo '<td style="text-align:left">';

            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
                echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/vendas/visualizar/' . $r->idVendas . '" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show bx-xs"></i></a>';
                echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/vendas/imprimir/' . $r->idVendas . '" target="_blank" class="btn-nwe6" title="Imprimir A4"><i class="bx bx-printer bx-xs"></i></a>';
                echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/vendas/imprimirTermica/' . $r->idVendas . '" target="_blank" class="btn-nwe6" title="Imprimir Não Fiscal"><i class="bx bx-printer bx-xs"></i></a>';
            }

            if ($r->faturado != 1 || $editavel) {
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
                    echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/vendas/editar/' . $r->idVendas . '" class="btn-nwe3" title="Editar venda"><i class="bx bx-edit bx-xs"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dVenda')) {
                    echo '<a href="#modal-excluir" role="button" data-toggle="modal" venda="' . $r->idVendas . '" class="btn-nwe4" title="Excluir Venda"><i class="bx bx-trash-alt bx-xs"></i></a>';
                }
            }
            echo '</td>';
            echo '</tr>';
        } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php echo $this->pagination->create_links(); ?>
</div>

<!-- Modal Excluir -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/vendas/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Venda</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idVenda" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir esta Venda?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true">
              <span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>

<!-- Modal Faturar em Massa -->
<div id="modal-faturar-massa" class="modal hide fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Faturar Vendas em Massa</h4>
            </div>
            <div class="modal-body">
                <div class="span12 alert alert-info" style="margin-left: 0">
                    Vendas selecionadas: <strong id="qtd-vendas-faturar">0</strong>
                </div>
                <div class="span12" style="margin-left: 0">
                    <div class="span6" style="margin-left: 0">
                        <label for="fm-vencimento">Data Entrada *</label>
                        <input class="span12 datepicker" autocomplete="off" id="fm-vencimento" type="text" name="fm-vencimento" value="<?= date('d/m/Y') ?>" />
                    </div>
                </div>
                <div class="span12" style="margin-left: 0">
                    <div class="span6" style="margin-left: 0">
                        <label for="fm-recebido">Foi Recebido?</label>
                        &nbsp;&nbsp;<input id="fm-recebido" type="checkbox" name="fm-recebido" value="1" />
                    </div>
                </div>
                <div id="fm-divRecebimento" class="span12" style="margin-left: 0; display: none;">
                    <div class="span6" style="margin-left: 0">
                        <label for="fm-recebimento">Data Recebimento</label>
                        <input class="span12 datepicker" autocomplete="off" id="fm-recebimento" type="text" name="fm-recebimento" value="<?= date('d/m/Y') ?>" />
                    </div>
                    <div class="span6">
                        <label for="fm-formaPgto">Forma Pgto *</label>
                        <select name="fm-formaPgto" id="fm-formaPgto" class="span12">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Cartão de Débito">Cartão de Débito</option>
                            <option value="Boleto">Boleto</option>
                            <option value="Depósito">Depósito</option>
                            <option value="Pix">Pix</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display:flex;justify-content: center">
                <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true" id="btn-cancelar-faturar-massa">
                    <span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span>
                </button>
                <button class="button btn btn-danger" id="btn-confirmar-faturar-massa">
                    <span class="button__icon"><i class="bx bx-dollar"></i></span><span class="button__text2">Faturar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a', function(event) {
            var venda = $(this).attr('venda');
            $('#idVenda').val(venda);
        });

        var statusFiltro = new URLSearchParams(window.location.search).get('status') || '';
        var temFiltro = statusFiltro !== '';

        if (!temFiltro) {
            $('#check-all').prop('disabled', true)
                           .attr('title', 'Filtre por um status para usar seleção em massa');
        }

        $('#check-all').on('click', function() {
            $('.bulk-item:not(:disabled)').prop('checked', this.checked);
            toggleBulkButton();
        });

        $(document).on('change', '.bulk-item', function() {
            toggleBulkButton();
        });

        function toggleBulkButton() {
            var checked = $('.bulk-item:checked').length;
            var hasStatus = $('#bulk-status').val() !== '';
            $('#btn-bulk-status').prop('disabled', !(checked > 0 && hasStatus));
        }

        $('#bulk-status').on('change', toggleBulkButton);

        $('#btn-bulk-status').on('click', function() {
            var ids = [];
            $('.bulk-item:checked').each(function() {
                ids.push($(this).val());
            });
            var status = $('#bulk-status').val();

            if (ids.length === 0 || !status) return;

            if (status === 'Faturado') {
                $('#qtd-vendas-faturar').text(ids.length);
                $('#modal-faturar-massa').modal('show');
                return;
            }

            Swal.fire({
                title: 'Alterar status em massa?',
                text: ids.length + ' venda(s) serão alterada(s) para "' + status + '"',
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, alterar!',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.value) {
                    $.ajax({
                        url: '<?= site_url('vendas/atualizarStatusEmMassa') ?>',
                        type: 'POST',
                        data: { ids: ids, status: status, filtroStatus: statusFiltro },
                        dataType: 'json',
                        success: function(data) {
                            if (data.result) {
                                Swal.fire({
                                    type: 'success',
                                    title: 'Sucesso',
                                    text: data.message
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    type: 'error',
                                    title: 'Erro',
                                    text: data.message
                                });
                            }
                        }
                    });
                }
            });
        });

        $('#fm-recebido').on('change', function() {
            if (this.checked) {
                $('#fm-divRecebimento').slideDown();
            } else {
                $('#fm-divRecebimento').slideUp();
            }
        });

        $('#btn-cancelar-faturar-massa').on('click', function() {
            $('#modal-faturar-massa').modal('hide');
        });

        $('#btn-confirmar-faturar-massa').on('click', function() {
            var vencimento = $('#fm-vencimento').val();
            if (!vencimento) {
                Swal.fire({ type: 'warning', title: 'Atenção', text: 'Data de entrada é obrigatória.' });
                return;
            }

            var recebido = $('#fm-recebido').is(':checked') ? 1 : 0;
            var recebimento = null;
            var formaPgto = null;

            if (recebido) {
                recebimento = $('#fm-recebimento').val();
                formaPgto = $('#fm-formaPgto').val();
                if (!recebimento || !formaPgto) {
                    Swal.fire({ type: 'warning', title: 'Atenção', text: 'Preencha data de recebimento e forma de pagamento.' });
                    return;
                }
            }

            var ids = [];
            $('.bulk-item:checked').each(function() {
                ids.push($(this).val());
            });

            $.ajax({
                url: '<?= site_url('vendas/faturarEmMassa') ?>',
                type: 'POST',
                data: {
                    ids: ids,
                    vencimento: vencimento,
                    recebido: recebido,
                    recebimento: recebimento,
                    formaPgto: formaPgto
                },
                dataType: 'json',
                success: function(data) {
                    $('#modal-faturar-massa').modal('hide');
                    if (data.result) {
                        Swal.fire({
                            type: 'success',
                            title: 'Sucesso',
                            text: data.message
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            type: 'error',
                            title: 'Erro',
                            text: data.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $('#modal-faturar-massa').modal('hide');
                    Swal.fire({
                        type: 'error',
                        title: 'Erro HTTP',
                        text: 'Status: ' + status + ' - ' + error
                    });
                }
            });
        });
    });
</script>
