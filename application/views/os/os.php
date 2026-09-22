<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/table-custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<style>
  select {
    width: 70px;
  }
  #data2 {
    margin-left: 4px;
  }
  .bulk-item:disabled {
    cursor: not-allowed;
    opacity: 0.5;
  }
</style>
<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
            <span class="icon">
                <i class="fas fa-diagnoses"></i>
            </span>
            <h5>Ordens de Serviço</h5>
        </div>
    <div class="span12" style="margin-left: 0">
        <form method="get" action="<?php echo base_url(); ?>index.php/os/gerenciar">
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aOs')) { ?>
            <div class="span2">
                <a href="<?php echo base_url(); ?>index.php/os/adicionar" class="button btn btn-mini btn-success" style="max-width: 160px">
                    <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Ordem de Serviço</span></a>
            </div>
            <?php
            } ?>

            <div class="span1">
                <button class="button btn btn-mini btn-warning" style="min-width: 70px">
                    <span class="button__icon"><i class='bx bx-search-alt'></i></span>
                    <span class="button__text2">Filtrar</span>
                </button>
            </div>

            <div class="span2">
                <input type="text" name="pesquisa" id="pesquisa" placeholder="Nome do cliente" class="span12" value="<?=set_value('pesquisa')?>">
            </div>
            <div class="span2">
                <select name="status" id="" class="span12">
                    <option value="">Status</option>
                    <option value="Aberto" <?=$this->input->get('status') == 'Aberto' ? 'selected' : ''?>>Aberto</option>
                    <option value="Faturado" <?=$this->input->get('status') == 'Faturado' ? 'selected' : ''?>>Faturado</option>
                    <option value="Negociação" <?=$this->input->get('status') == 'Negociação' ? 'selected' : ''?>>Negociação</option>
                    <option value="Em Andamento" <?=$this->input->get('status') == 'Em Andamento' ? 'selected' : ''?>>Em Andamento</option>
                    <option value="Orçamento" <?=$this->input->get('status') == 'Orçamento' ? 'selected' : ''?>>Orçamento</option>
                    <option value="Finalizado" <?=$this->input->get('status') == 'Finalizado' ? 'selected' : ''?>>Finalizado</option>
                    <option value="Cancelado" <?=$this->input->get('status') == 'Cancelado' ? 'selected' : ''?>>Cancelado</option>
                    <option value="Aguardando Peças" <?=$this->input->get('status') == 'Aguardando Peças' ? 'selected' : ''?>>Aguard. Peças</option>
                    <option value="Aprovado" <?=$this->input->get('status') == 'Aprovado' ? 'selected' : ''?>>Aprovado</option>
                </select>

            </div>

            <div class="span2">
                <input type="text" name="data" autocomplete="off" id="data" placeholder="Data Inicial" class="span12 datepicker" value="<?=$this->input->get('data')?>">
            </div>
            <div class="span2">
                <input type="text" name="data2" autocomplete="off" id="data2" placeholder="Data Final" class="span12 datepicker" value="<?=$this->input->get('data2')?>">
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

    <div class="widget-box" style="margin-top: 8px">
        <div class="widget-content nopadding">
            <div class="table-responsive">
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th style="width:20px; text-align:center"><input type="checkbox" id="check-all"></th>
                            <th>N°</th>
                            <th>Cliente</th>
                            <th class="ph1">Responsável</th>
                            <th>Data Inicial</th>
                            <th class="ph2">Data Final</th>
                            <th class="ph3">Venc. Garantia</th>
                            <th>Valor Total</th>
                            <th>Desconto</th>
                            <th>Valor com Desconto</th>
                            <th class="ph4">V.T (Faturado)</th>
                            <th>Status</th>
                            <th style="white-space:nowrap">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$results) {
                            echo '<tr>
                            <td colspan="10">Nenhuma OS Cadastrada</td>
                            </tr>';
                        }

$this->load->model('os_model');
foreach ($results as $r) {
    $dataInicial = date(('d/m/Y'), strtotime($r->dataInicial));
    if ($r->dataFinal != null) {
        $dataFinal = date(('d/m/Y'), strtotime($r->dataFinal));
    } else {
        $dataFinal = "";
    }
    if ($this->input->get('pesquisa') === null && is_array(json_decode($configuration['os_status_list']))) {
        if (in_array($r->status, json_decode($configuration['os_status_list'])) != true) {
            continue;
        }
    }

    switch ($r->status) {
        case 'Aberto':
            $cor = '#00cd00';
            break;
        case 'Em Andamento':
            $cor = '#436eee';
            break;
        case 'Orçamento':
            $cor = '#CDB380';
            break;
        case 'Negociação':
            $cor = '#AEB404';
            break;
        case 'Cancelado':
            $cor = '#CD0000';
            break;
        case 'Finalizado':
            $cor = '#256';
            break;
        case 'Faturado':
            $cor = '#B266FF';
            break;
        case 'Aguardando Peças':
            $cor = '#FF7F00';
            break;
        case 'Aprovado':
            $cor = '#808080';
            break;
        default:
            $cor = '#E0E4CC';
            break;
    }
    $vencGarantia = '';

    if ($r->garantia && is_numeric($r->garantia)) {
        $vencGarantia = dateInterval($r->dataFinal, $r->garantia);
    }
    $corGarantia = '';
    if (!empty($vencGarantia)) {
        $dataGarantia = explode('/', $vencGarantia);
        $dataGarantiaFormatada = $dataGarantia[2] . '-' . $dataGarantia[1] . '-' . $dataGarantia[0];
        if (strtotime($dataGarantiaFormatada) >= strtotime(date('d-m-Y'))) {
            $corGarantia = '#4d9c79';
        } else {
            $corGarantia = '#f24c6f';
        }
    } elseif ($r->garantia == "0") {
        $vencGarantia = 'Sem Garantia';
        $corGarantia = '';
    } else {
        $vencGarantia = '';
        $corGarantia = '';
    }

    $editavel = $this->os_model->isEditable($r->idOs);

    $checkboxDisabled = $editavel ? '' : ' disabled';
    $titleAttr = $editavel ? '' : ' title="OS não editável"';

    echo '<tr>';
    echo '<td style="text-align:center; padding:2px"><input type="checkbox" class="bulk-item" value="' . $r->idOs . '"' . $checkboxDisabled . $titleAttr . '></td>';
    echo '<td>' . $r->idOs . '</td>';
    echo '<td class="cli1"><a href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '" style="margin-right: 1%">' . $r->nomeCliente . '</a></td>';
    echo '<td class="ph1">' . $r->nome . '</td>';
    echo '<td>' . $dataInicial . '</td>';
    echo '<td class="ph2">' . $dataFinal . '</td>';
    echo '<td class="ph3"><span class="badge" style="background-color: ' . $corGarantia . '; border-color: ' . $corGarantia . '">' . $vencGarantia . '</span> </td>';
    echo '<td>R$ ' . number_format($r->totalProdutos + $r->totalServicos, 2, ',', '.') . '</td>';
    echo '<td>R$ ' . number_format(floatval($r->desconto), 2, ',', '.') . '</td>';
    echo '<td>R$ ' . number_format(floatval($r->valor_desconto), 2, ',', '.') . '</td>';
    echo '<td class="ph4">R$ ' . number_format($r->faturado ? floatval($r->valor_desconto) : 0.00, 2, ',', '.') . '</td>';
    echo '<td><span class="badge" style="background-color: ' . $cor . '; border-color: ' . $cor . '">' . $r->status . '</span> </td>';
    echo '<td>';

    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
        echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/visualizar/' . $r->idOs . '" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show"></i></a>';
        echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/imprimir/' . $r->idOs . '" target="_blank" class="btn-nwe6" title="Imprimir A4"><i class="bx bx-printer bx-xs"></i></a>';
        echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/imprimirTermica/' . $r->idOs . '" target="_blank" class="btn-nwe6" title="Imprimir Não Fiscal"><i class="bx bx-printer bx-xs"></i></a>';
    }
    if ($editavel) {
        echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/editar/' . $r->idOs . '" class="btn-nwe3" title="Editar OS"><i class="bx bx-edit"></i></a>';
    }
    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dOs') && $editavel) {
        echo '<a href="#modal-excluir" role="button" data-toggle="modal" os="' . $r->idOs . '" class="btn-nwe4" title="Excluir OS"><i class="bx bx-trash-alt"></i></a>  ';
    }
    echo '</td>';
    echo '</tr>';
} ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php echo $this->pagination->create_links(); ?>

    <!-- Modal -->
    <div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <form action="<?php echo base_url() ?>index.php/os/excluir" method="post">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h5 id="myModalLabel">Excluir OS</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" id="idOs" name="id" value="" />
                <h5 style="text-align: center">Deseja realmente excluir esta OS?</h5>
            </div>
            <div class="modal-footer" style="display:flex;justify-content: center">
                <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true">
                    <span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
                <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
            </div>
        </form>
    </div>
</div>

<div id="modal-faturar-massa" class="modal hide fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Faturar OS em Massa</h4>
            </div>
            <div class="modal-body">
                <div class="span12 alert alert-info" style="margin-left: 0">
                    OS selecionadas: <strong id="qtd-os-faturar">0</strong>
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
            var os = $(this).attr('os');
            $('#idOs').val(os);
        });
        $(document).on('click', '#excluir-notificacao', function(event) {
            event.preventDefault();
            $.ajax({
                    url: '<?php echo site_url() ?>/os/excluir_notificacao',
                    type: 'GET',
                    dataType: 'json',
                })
                .done(function(data) {
                    if (data.result == true) {
                        Swal.fire({
                            type: "success",
                            title: "Sucesso",
                            text: "Notificação excluída com sucesso."
                        });
                        location.reload();
                    } else {
                        Swal.fire({
                            type: "success",
                            title: "Sucesso",
                            text: "Ocorreu um problema ao tentar exlcuir notificação."
                        });
                    }
                });
        });
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
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
                $('#qtd-os-faturar').text(ids.length);
                $('#modal-faturar-massa').modal('show');
                return;
            }

            Swal.fire({
                title: 'Alterar status em massa?',
                text: ids.length + ' OS serão alteradas para "' + status + '"',
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, alterar!',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.value) {
                    $.ajax({
                        url: '<?= site_url('os/atualizarStatusEmMassa') ?>',
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
                url: '<?= site_url('os/faturarEmMassa') ?>',
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
                        text: 'Status: ' + status + ' - ' + error + '\nResponse: ' + xhr.responseText.substring(0, 200)
                    });
                }
            });
        });
    });
</script>
