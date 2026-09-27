<?php
$statusInicial = set_value('status', 'Orçamento');
if (! in_array($statusInicial, ['Orçamento', 'Aprovado'], true)) {
    $statusInicial = 'Orçamento';
}

$itensIniciais = is_array($itensAnteriores ?? null) ? $itensAnteriores : [];

$vnConfig = [
    'buscaProduto' => site_url('vendas/autoCompleteProduto'),
    'autoCompleteCliente' => site_url('vendas/autoCompleteCliente'),
    'autoCompleteUsuario' => site_url('vendas/autoCompleteUsuario'),
    'controlEstoque' => ! empty($configuration['control_estoque']),
    'itensIniciais' => $itensIniciais,
];
?>
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/vendas-nova.css?v=<?php echo (int) @filemtime(FCPATH . 'assets/css/vendas-nova.css'); ?>" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url(); ?>assets/js/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/trumbowyg/ui/trumbowyg.css">
<script type="text/javascript" src="<?php echo base_url(); ?>assets/trumbowyg/trumbowyg.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/trumbowyg/langs/pt_br.js"></script>

<div class="vn-page">
    <form action="<?php echo current_url(); ?>" method="post" id="formNovaVenda" autocomplete="off">
        <input type="hidden" name="itens_json" id="itens_json" value="" />
        <input type="hidden" name="faturar" id="faturar" value="0" />

        <div class="vn-header">
        <div class="vn-title">
            <i class="fas fa-cash-register"></i>
            <div>
                <h4>Nova Venda</h4>
                <span class="vn-subtitle">Cliente, itens e pagamento em uma única tela — sem etapas.</span>
            </div>
        </div>

        <div class="vn-header-actions">
            <div class="vn-segmented" role="radiogroup" aria-label="Status da venda">
                <label class="vn-seg <?= $statusInicial === 'Orçamento' ? 'is-active' : '' ?>">
                    <input type="radio" name="status" value="Orçamento" <?= $statusInicial === 'Orçamento' ? 'checked' : '' ?>>
                    <i class="bx bx-file"></i> Orçamento
                </label>
                <label class="vn-seg <?= $statusInicial === 'Aprovado' ? 'is-active' : '' ?>">
                    <input type="radio" name="status" value="Aprovado" <?= $statusInicial === 'Aprovado' ? 'checked' : '' ?>>
                    <i class="bx bx-check-circle"></i> Aprovado
                </label>
            </div>

            <div class="vn-btns">
                <a href="<?php echo site_url('vendas'); ?>" class="btn vn-btn-ghost">Voltar</a>
                <button type="submit" class="btn btn-primary vn-btn" id="btnSalvar">
                    <i class="bx bx-save"></i> <span>Salvar</span>
                </button>
                <button type="button" class="btn btn-success vn-btn" id="btnFaturarAgora">
                    <i class="bx bx-dollar"></i> <span>Salvar e Faturar</span>
                </button>
            </div>
        </div>
    </div>

    <?php if (! empty($custom_error)) { ?>
        <div class="vn-alert alert alert-error"><?php echo $custom_error; ?></div>
    <?php } ?>

        <div class="vn-grid">
            <div class="vn-col-main">
                <section class="vn-card">
                    <header class="vn-card-head">
                        <h5><i class="bx bx-cart"></i> Itens da venda</h5>
                        <span class="vn-pill" id="vn-itens-count">0 itens</span>
                    </header>

                    <div class="vn-card-body">
                        <div class="vn-search">
                            <i class="bx bx-search"></i>
                            <input type="text" id="vn-busca-produto" class="vn-input vn-input--search"
                                placeholder="Código de barras ou nome do produto — pressione Enter para adicionar"
                                aria-label="Buscar produto" />
                        </div>
                        <div id="vn-alertas" role="alert"></div>

                        <div class="vn-table-wrap">
                            <table class="vn-table">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th class="vn-num">Qtd</th>
                                        <th class="vn-num">Preço unit.</th>
                                        <th class="vn-num">Total</th>
                                        <th class="vn-act"><span class="sr">Ações</span></th>
                                    </tr>
                                </thead>
                                <tbody id="vn-itens-corpo"></tbody>
                            </table>

                            <div class="vn-empty" id="vn-empty">
                                <i class="bx bx-basket"></i>
                                <p>Nenhum item adicionado ainda</p>
                                <span>Busque pelo nome ou escaneie o código de barras para começar.</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="vn-card">
                    <button type="button" class="vn-card-head vn-acc" id="vn-obs-toggle" aria-expanded="false" aria-controls="vn-obs-corpo">
                        <h5><i class="bx bx-note"></i> Observações</h5>
                        <span class="vn-acc-label">Opcional — interna e ao cliente <i class="bx bx-chevron-down vn-acc-ico"></i></span>
                    </button>

                    <div class="vn-card-body vn-obs-body" id="vn-obs-corpo" hidden>
                        <div class="vn-obs-bar">
                            <div class="vn-tabs-mini" role="tablist">
                                <button type="button" class="vn-tab is-active" data-obs="observacoes" role="tab">Interna</button>
                                <button type="button" class="vn-tab" data-obs="observacoes_cliente" role="tab">Ao cliente</button>
                            </div>
                            <button type="button" class="vn-btn-mini" id="vn-formatar" title="Habilitar formatação de texto">
                                <i class="bx bx-bold"></i> Formatar
                            </button>
                        </div>

                        <label class="vn-label vn-label--visually-hidden" for="observacoes">Observações internas</label>
                        <textarea class="vn-obs" id="observacoes" name="observacoes" rows="2"
                            placeholder="Notas internas — não aparecem para o cliente"><?php echo html_escape(set_value('observacoes')); ?></textarea>

                        <label class="vn-label vn-label--visually-hidden" for="observacoes_cliente">Observações ao cliente</label>
                        <textarea class="vn-obs vn-is-hidden" id="observacoes_cliente" name="observacoes_cliente" rows="2"
                            placeholder="Mensagem que será impressa/visualizada pelo cliente"><?php echo html_escape(set_value('observacoes_cliente')); ?></textarea>
                    </div>
                </section>
            </div>

            <aside class="vn-col-side">
                <section class="vn-card">
                    <header class="vn-card-head">
                        <h5><i class="bx bx-user"></i> Cliente e dados da venda</h5>
                    </header>
                    <div class="vn-card-body">
                        <div class="vn-field">
                            <label class="vn-label" for="cliente">Cliente <span class="vn-req">*</span></label>
                            <input type="text" id="cliente" name="cliente" class="vn-input"
                                value="<?php echo html_escape(set_value('cliente')); ?>"
                                placeholder="Nome, documento ou telefone" />
                            <input type="hidden" name="clientes_id" id="clientes_id" value="<?php echo html_escape(set_value('clientes_id')); ?>" />
                            <p class="vn-hint">Selecione na lista para vincular a venda ao cadastro.</p>
                            <div class="vn-inline-actions">
                                <a class="vn-link" href="<?php echo site_url('clientes/adicionar'); ?>" target="_blank" rel="noopener">
                                    <i class="bx bx-plus"></i> Novo cliente
                                </a>
                            </div>
                        </div>

                        <div class="vn-sep"></div>

                        <div class="vn-field">
                            <label class="vn-label" for="dataVenda">Data da venda <span class="vn-req">*</span></label>
                            <input type="text" id="dataVenda" name="dataVenda" class="vn-input vn-datepicker"
                                value="<?php echo html_escape(set_value('dataVenda', date('d/m/Y'))); ?>" />
                        </div>

                        <div class="vn-field">
                            <label class="vn-label" for="tecnico">Vendedor <span class="vn-req">*</span></label>
                            <input type="text" id="tecnico" name="tecnico" class="vn-input"
                                value="<?php echo html_escape(set_value('tecnico', $this->session->userdata('nome_admin'))); ?>" />
                            <input type="hidden" name="usuarios_id" id="usuarios_id"
                                value="<?php echo html_escape(set_value('usuarios_id', $this->session->userdata('id_admin'))); ?>" />
                        </div>

                        <div class="vn-field">
                            <label class="vn-label" for="garantia">Garantia (dias)</label>
                            <input type="number" id="garantia" name="garantia" class="vn-input" min="0" max="9999"
                                placeholder="Opcional" value="<?php echo html_escape(set_value('garantia')); ?>" />
                        </div>
                    </div>
                </section>

                <section class="vn-card vn-card--resumo">
                    <header class="vn-card-head">
                        <h5><i class="bx bx-calculator"></i> Resumo</h5>
                    </header>
                    <div class="vn-card-body">
                        <div class="vn-resumo-row">
                            <span>Subtotal</span>
                            <strong id="vn-subtotal">R$ 0,00</strong>
                        </div>

                        <div class="vn-resumo-row vn-resumo-row--input">
                            <label for="desconto">Desconto</label>
                            <div class="vn-desconto">
                                <input type="text" inputmode="decimal" id="desconto" name="desconto" class="vn-input vn-input--sm"
                                    min="0" step="0.01" placeholder="0,00" value="<?php echo html_escape(set_value('desconto')); ?>" />
                                <select name="tipoDesconto" id="tipoDesconto" class="vn-input vn-input--sm">
                                    <option value="real" <?= set_value('tipoDesconto') === 'porcento' ? '' : 'selected' ?>>R$</option>
                                    <option value="porcento" <?= set_value('tipoDesconto') === 'porcento' ? 'selected' : '' ?>>%</option>
                                </select>
                            </div>
                        </div>

                        <div class="vn-resumo-total">
                            <span>Total líquido</span>
                            <strong id="vn-total">R$ 0,00</strong>
                            <small id="vn-desconto-info"></small>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <div id="vn-modal-faturar" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="vnTituloFaturar" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="vnTituloFaturar">Salvar e Faturar</h3>
            </div>
            <div class="modal-body">
                <div class="vn-alert alert alert-info">O faturamento gera a receita no Financeiro e dá baixa no estoque.</div>

                <div class="vn-modal-total">
                    <span>Total a faturar</span>
                    <strong id="vn-faturar-total">R$ 0,00</strong>
                </div>

                <div class="vn-field">
                    <label class="vn-label" for="vn-vencimento">Data de entrada <span class="vn-req">*</span></label>
                    <input type="text" id="vn-vencimento" name="vencimento" class="vn-input vn-datepicker" placeholder="dd/mm/aaaa" />
                </div>

                <label class="vn-check">
                    <input type="checkbox" id="vn-recebido" name="recebido" value="1" />
                    Recebido agora (entra no Caixa)
                </label>

                <div class="vn-field vn-is-hidden" id="vn-campos-recebimento">
                    <div class="vn-field">
                        <label class="vn-label" for="vn-recebimento">Data de recebimento</label>
                        <input type="text" id="vn-recebimento" name="recebimento" class="vn-input vn-datepicker" placeholder="dd/mm/aaaa" />
                    </div>
                    <div class="vn-field">
                        <label class="vn-label" for="vn-forma-pgto">Forma de pagamento</label>
                        <select name="formaPgto" id="vn-forma-pgto" class="vn-input">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Pix">Pix</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Débito">Débito</option>
                            <option value="Boleto">Boleto</option>
                            <option value="Depósito">Depósito</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>

                <p class="vn-hint">A descrição da receita é gerada automaticamente como “Fatura de Venda #ID - Cliente”.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
                <button type="submit" class="btn btn-success vn-btn" id="vn-confirmar-faturar">
                    <i class="bx bx-dollar"></i> <span>Confirmar e faturar</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    window.VN = <?php echo json_encode($vnConfig, JSON_UNESCAPED_UNICODE); ?>;
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/vendas-nova.js?v=<?php echo (int) @filemtime(FCPATH . 'assets/js/vendas-nova.js'); ?>"></script>
