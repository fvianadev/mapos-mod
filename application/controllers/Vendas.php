<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vendas extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('vendas_model');
        $this->data['menuVendas'] = 'Vendas';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->load->library('pagination');

        $where_array = [];

        $pesquisa = $this->input->get('pesquisa');
        $status = $this->input->get('status');
        $de = $this->input->get('data');
        $ate = $this->input->get('data2');

        if ($pesquisa) {
            $where_array['pesquisa'] = $pesquisa;
        }
        if ($status) {
            $where_array['status'] = $status;
        }
        if ($de) {
            $where_array['de'] = $de;
        }
        if ($ate) {
            $where_array['ate'] = $ate;
        }

        $this->data['configuration']['base_url'] = site_url('vendas/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->vendas_model->count('vendas');
        
        if (count($where_array) > 0) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}&status={$status}&data={$de}&data2={$ate}";
            $this->data['configuration']['first_url'] = base_url("index.php/vendas/gerenciar")."?pesquisa={$pesquisa}&status={$status}&data={$de}&data2={$ate}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->vendas_model->get('vendas', '*', $where_array, $this->data['configuration']['per_page'], $this->uri->segment(3));

        foreach ($this->data['results'] as $key => $venda) {
            $this->data['results'][$key]->totalProdutos = $this->vendas_model->getTotalVendas($venda->idVendas);
        }

        $this->data['view'] = 'vendas/vendas';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar Vendas.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        $this->data['itensAnteriores'] = [];

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('dataVenda', 'Data da Venda', 'required|trim', [
                'required' => 'Informe a data da venda.',
            ]);
            $this->form_validation->set_rules('clientes_id', 'Cliente', 'required|trim|numeric', [
                'required' => 'Selecione o cliente na lista.',
            ]);
            $this->form_validation->set_rules('usuarios_id', 'Vendedor', 'required|trim|numeric', [
                'required' => 'Informe o vendedor.',
            ]);
            $this->form_validation->set_rules('status', 'Status', 'required|trim', [
                'required' => 'Escolha o status da venda.',
            ]);
            $this->form_validation->set_rules('garantia', 'Garantia', 'trim|integer', [
                'integer' => 'A garantia deve ser um número inteiro de dias.',
            ]);
            $this->form_validation->set_rules('desconto', 'Desconto', 'trim|numeric', [
                'numeric' => 'O desconto deve ser um valor numérico (use ponto como separador decimal).',
            ]);
            $this->form_validation->set_rules('tipoDesconto', 'Tipo de Desconto', 'trim');
            $this->form_validation->set_rules('observacoes', 'Observações Internas', 'trim');
            $this->form_validation->set_rules('observacoes_cliente', 'Observações ao Cliente', 'trim');
            $this->form_validation->set_rules('itens_json', 'Itens da venda', 'required|trim', [
                'required' => 'Adicione ao menos um produto à venda.',
            ]);

            $faturar = $this->input->post('faturar') == 1;
            $this->form_validation->set_rules('cliente', 'Cliente', 'trim');
            if ($faturar) {
                $this->form_validation->set_rules('vencimento', 'Data de Entrada', 'required', [
                    'required' => 'Informe a data de entrada para faturar.',
                ]);
            }

            $status = (string) $this->input->post('status');
            $itens = $this->decodeItens($this->input->post('itens_json'));
            $this->data['itensAnteriores'] = is_array($itens) ? $itens : [];

            if ($this->form_validation->run() == false) {
                $this->data['custom_error'] = validation_errors('<p>', '</p>');
                if (! $this->data['custom_error']) {
                    $this->data['custom_error'] = '<p>Verifique os campos destacados e informe ao menos um produto.</p>';
                }
            } elseif (! in_array($status, ['Orçamento', 'Aprovado'], true)) {
                $this->data['custom_error'] = '<p>Status inválido. Escolha Orçamento ou Aprovado.</p>';
            } elseif ($itens === null) {
                $this->data['custom_error'] = '<p>A lista de itens enviada é inválida.</p>';
            } elseif (empty($itens)) {
                $this->data['custom_error'] = '<p>Adicione ao menos um produto à venda.</p>';
            } elseif (($erroItens = $this->validarItens($itens)) !== null) {
                $this->data['custom_error'] = '<p>' . html_escape($erroItens) . '</p>';
            } else {
                $resultado = $this->salvarNovaVenda($itens, $status, $faturar);

                if ($resultado['result']) {
                    $this->session->set_flashdata('success', $resultado['message']);
                    log_info('Adicionou uma venda. ID: ' . $resultado['idVendas']);

                    redirect(site_url('vendas/visualizar/' . $resultado['idVendas']));
                }

                $this->data['custom_error'] = '<p>' . html_escape($resultado['message']) . '</p>';
            }
        }

        $this->data['view'] = 'vendas/novaVenda';

        return $this->layout();
    }

    private function decodeItens($json)
    {
        if (! is_string($json) || trim($json) === '') {
            return [];
        }

        $itens = json_decode($json, true);

        return is_array($itens) ? $itens : null;
    }

    private function validarItens(array $itens)
    {
        $quantidades = [];

        foreach ($itens as $item) {
            $produtosId = $item['produtos_id'] ?? null;
            $quantidade = $item['quantidade'] ?? null;
            $preco = $item['preco'] ?? null;

            if (! is_numeric($produtosId) || (int) $produtosId <= 0) {
                return 'Um dos itens não tem um produto válido.';
            }
            if (filter_var($quantidade, FILTER_VALIDATE_INT) === false || (int) $quantidade <= 0) {
                return 'A quantidade de todos os itens deve ser um número inteiro maior que zero.';
            }
            if (! is_numeric($preco) || (float) $preco < 0) {
                return 'O preço de todos os itens deve ser igual ou maior que zero.';
            }

            $produtosId = (int) $produtosId;
            $quantidades[$produtosId] = ($quantidades[$produtosId] ?? 0) + (int) $quantidade;
        }

        if (! $this->data['configuration']['control_estoque']) {
            return null;
        }

        $produtos = $this->db->where_in('idProdutos', array_keys($quantidades))->get('produtos')->result();

        if (count($produtos) !== count($quantidades)) {
            return 'Um dos produtos da lista não foi encontrado.';
        }

        foreach ($produtos as $produto) {
            if ((int) $produto->saida !== 1) {
                return "O produto \"{$produto->descricao}\" não está habilitado para saída/venda.";
            }
            if ((float) $produto->estoque < $quantidades[$produto->idProdutos]) {
                return "Estoque insuficiente para \"{$produto->descricao}\" (disponível: " . number_format((float) $produto->estoque, 2, ',', '.') . ').';
            }
        }

        return null;
    }

    private function calcularTotaisVenda(array $itens)
    {
        $subtotal = 0.0;
        foreach ($itens as $item) {
            $subtotal += (float) $item['quantidade'] * (float) $item['preco'];
        }
        $subtotal = round($subtotal, 2);

        $desconto = (float) $this->input->post('desconto');
        $tipoDesconto = $this->input->post('tipoDesconto') === 'porcento' ? 'porcento' : 'real';
        $descontoValor = $tipoDesconto === 'porcento' ? $subtotal * ($desconto / 100) : $desconto;
        $descontoValor = round(min($subtotal, max(0, $descontoValor)), 2);

        return [
            'subtotal' => $subtotal,
            'desconto' => $desconto,
            'tipo_desconto' => $tipoDesconto,
            'desconto_valor' => $descontoValor,
            'liquido' => round($subtotal - $descontoValor, 2),
        ];
    }

    private function salvarNovaVenda(array $itens, $status, $faturar)
    {
        $data = DateTime::createFromFormat('d/m/Y', (string) $this->input->post('dataVenda'));
        if (! $data instanceof DateTime) {
            return ['result' => false, 'message' => 'Data da venda inválida.'];
        }
        $dataVenda = $data->format('Y-m-d');

        $vencimento = null;
        $recebimento = null;

        if ($faturar) {
            $vencimentoInfo = DateTime::createFromFormat('d/m/Y', (string) $this->input->post('vencimento'));
            if (! $vencimentoInfo instanceof DateTime) {
                return ['result' => false, 'message' => 'Data de entrada inválida.'];
            }
            $vencimento = $vencimentoInfo->format('Y-m-d');

            $recebimentoInfo = (string) $this->input->post('recebimento');
            if ($recebimentoInfo !== '') {
                $recebimentoData = DateTime::createFromFormat('d/m/Y', $recebimentoInfo);
                if (! $recebimentoData instanceof DateTime) {
                    return ['result' => false, 'message' => 'Data de recebimento inválida.'];
                }
                $recebimento = $recebimentoData->format('Y-m-d');
            }
        }

        $totais = $this->calcularTotaisVenda($itens);
        $garantia = $this->input->post('garantia');

        $venda = [
            'dataVenda' => $dataVenda,
            'observacoes' => $this->input->post('observacoes'),
            'observacoes_cliente' => $this->input->post('observacoes_cliente'),
            'clientes_id' => (int) $this->input->post('clientes_id'),
            'usuarios_id' => (int) $this->input->post('usuarios_id'),
            'faturado' => 0,
            'status' => $status,
            'garantia' => ($garantia === null || $garantia === '') ? null : (int) $garantia,
            'desconto' => $totais['desconto_valor'] > 0 ? number_format($totais['desconto'], 2, '.', '') : '0.00',
            'tipo_desconto' => $totais['desconto_valor'] > 0 ? $totais['tipo_desconto'] : null,
            'valorTotal' => number_format($totais['subtotal'], 2, '.', ''),
            'valor_desconto' => number_format($totais['liquido'], 2, '.', ''),
        ];

        $erro = null;

        $this->db->trans_start();

        $idVenda = $this->vendas_model->add('vendas', $venda, true);

        if (! is_numeric($idVenda)) {
            $erro = 'Não foi possível criar a venda.';
        } else {
            foreach ($itens as $item) {
                $quantidade = (int) $item['quantidade'];
                $preco = (float) $item['preco'];

                $itemSalvo = $this->vendas_model->add('itens_de_vendas', [
                    'quantidade' => $quantidade,
                    'subTotal' => number_format($quantidade * $preco, 2, '.', ''),
                    'produtos_id' => (int) $item['produtos_id'],
                    'preco' => number_format($preco, 2, '.', ''),
                    'vendas_id' => $idVenda,
                ]);

                if (! $itemSalvo) {
                    $erro = 'Não foi possível salvar um dos itens da venda.';
                    break;
                }
            }
        }

        if ($erro === null && $faturar) {
            $faturamento = $this->vendas_model->faturarVenda($idVenda, [
                'vencimento' => $vencimento,
                'recebimento' => $recebimento,
                'recebido' => $this->input->post('recebido') == 1,
                'formaPgto' => $this->input->post('formaPgto'),
                'descricao' => $this->input->post('descricao'),
                'usuarios_id' => $this->session->userdata('id_admin'),
            ]);

            if (! $faturamento['result']) {
                $erro = $faturamento['message'];
            }
        }

        if ($erro !== null) {
            $this->db->trans_rollback();

            return ['result' => false, 'message' => $erro];
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['result' => false, 'message' => 'Erro ao gravar a venda. Operação revertida.'];
        }

        if ($faturar) {
            return ['result' => true, 'idVendas' => $idVenda, 'message' => "Venda #{$idVenda} criada e faturada com sucesso! Receita gerada e estoque atualizado."];
        }

        return ['result' => true, 'idVendas' => $idVenda, 'message' => "Venda #{$idVenda} criada com sucesso!"];
    }

    public function editar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3)) || ! $this->vendas_model->getById($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Venda não encontrada ou parâmetro inválido.');
            redirect('vendas/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar vendas');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->data['editavel'] = $this->vendas_model->isEditable($this->input->post('idVendas'));
        if (! $this->data['editavel']) {
            $this->session->set_flashdata('error', 'Essa Venda já tem seu status Faturada e não pode ser alterado e nem suas informações atualizadas. Por favor abrir uma nova Venda.');

            redirect(site_url('vendas'));
        }

        if ($this->form_validation->run('vendas') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $idVenda = $this->input->post('idVendas');
            $vendaAtual = $this->vendas_model->getById($idVenda);
            $status = $this->input->post('status');

            if (! $vendaAtual) {
                $this->data['custom_error'] = '<div class="form_error"><p>Venda não encontrada.</p></div>';
            } elseif (! $this->vendas_model->statusEhValido($status)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Status inválido para vendas. Use: Orçamento, Aprovado, Faturado ou Cancelado.</p></div>';
            } elseif ($status === 'Faturado' && (int) $vendaAtual->faturado !== 1) {
                $this->data['custom_error'] = '<div class="form_error"><p>Use o botão "Faturar" para gerar a receita e concluir a venda.</p></div>';
            } elseif ((int) $vendaAtual->faturado === 1 && ! in_array($status, ['Faturado', 'Cancelado'], true)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Venda já faturada: ela só pode permanecer Faturada ou ser Cancelada.</p></div>';
            } else {
                $dataVenda = $this->input->post('dataVenda');

                try {
                    $dataVenda = explode('/', $dataVenda);
                    $dataVenda = $dataVenda[2] . '-' . $dataVenda[1] . '-' . $dataVenda[0];
                } catch (Exception $e) {
                    $dataVenda = date('Y/m/d');
                }

                $data = [
                    'dataVenda' => $dataVenda,
                    'observacoes' => $this->input->post('observacoes'),
                    'observacoes_cliente' => $this->input->post('observacoes_cliente'),
                    'usuarios_id' => $this->input->post('usuarios_id'),
                    'clientes_id' => $this->input->post('clientes_id'),
                    'status' => $status,
                    'garantia' => $this->input->post('garantia')
                ];

                $this->db->trans_start();

                $editado = $this->vendas_model->edit('vendas', $data, 'idVendas', $idVenda);

                if ($editado && $status === 'Cancelado' && $vendaAtual->status !== 'Cancelado') {
                    $this->vendas_model->estornarFaturamento($idVenda);
                }

                $this->db->trans_complete();

                if ($editado && $this->db->trans_status() !== false) {
                    $this->session->set_flashdata('success', 'Venda editada com sucesso!');
                    log_info('Alterou uma venda. ID: ' . $idVenda);
                    redirect(site_url('vendas/editar/') . $idVenda);
                } else {
                    $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
                }
            }
        }

        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['view'] = 'vendas/editarVenda';

        return $this->layout();
    }

    public function visualizar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['qrCode'] = $this->vendas_model->getQrCode(
            $this->uri->segment(3),
            $this->data['configuration']['pix_key'],
            $this->data['emitente']
        );
        $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);
        $this->data['modalGerarPagamento'] = $this->load->view(
            'cobrancas/modalGerarPagamento',
            [
                'id' => $this->uri->segment(3),
                'tipo' => 'venda',
            ],
            true
        );

        $clienteId = $this->data['result']->clientes_id;
        $this->load->model('clientes_model');
        $cliente = $this->clientes_model->getById($clienteId);

        $zapnumber = preg_replace('/[^0-9]/', '', $cliente->telefone ?? '');
        $this->data['zapnumber'] = $zapnumber;
        $this->data['view'] = 'vendas/visualizarVenda';

        return $this->layout();
    }

    public function imprimir()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['qrCode'] = $this->vendas_model->getQrCode(
            $this->uri->segment(3),
            $this->data['configuration']['pix_key'],
            $this->data['emitente']
        );
        $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);

        $this->load->view('vendas/imprimirVenda', $this->data);
    }

    public function imprimirTermica()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['qrCode'] = $this->vendas_model->getQrCode(
            $this->uri->segment(3),
            $this->data['configuration']['pix_key'],
            $this->data['emitente']
        );
        
        $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);

        $this->load->view('vendas/imprimirVendaTermica', $this->data);
    }

    public function imprimirVendaOrcamento()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['qrCode'] = $this->vendas_model->getQrCode(
            $this->uri->segment(3),
            $this->data['configuration']['pix_key'],
            $this->data['emitente']
        );
        
        $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);
        $this->load->view('vendas/imprimirVendaOrcamento', $this->data);
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir vendas');
            redirect(base_url());
        }

        $this->load->model('vendas_model');

        $id = $this->input->post('id');

        $editavel = $this->vendas_model->isEditable($id);
        if (! $editavel) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir. Venda já faturada');
            redirect(site_url('vendas/gerenciar/'));
        }

        $venda = $this->vendas_model->getByIdCobrancas($id);
        if ($venda == null) {
            $venda = $this->vendas_model->getById($id);
            if ($venda == null) {
                $this->session->set_flashdata('error', 'Erro ao tentar excluir venda.');
                redirect(site_url('vendas/gerenciar/'));
            }
        }

        if (isset($venda->idCobranca) != null) {
            if ($venda->status == 'canceled') {
                $this->vendas_model->delete('cobrancas', 'vendas_id', $id);
            } else {
                $this->session->set_flashdata('error', 'Existe uma cobrança associada a esta venda, deve cancelar e/ou excluir a cobrança primeiro!');
                redirect(site_url('vendas/gerenciar/'));
            }
        }

        if ((int) $venda->faturado === 1) {
            $this->vendas_model->estornarFaturamento($id);
        }

        $this->vendas_model->delete('itens_de_vendas', 'vendas_id', $id);
        $this->vendas_model->delete('vendas', 'idVendas', $id);

        log_info('Removeu uma venda. ID: ' . $id);

        $this->session->set_flashdata('success', 'Venda excluída com sucesso!');
        redirect(site_url('vendas/gerenciar/'));
    }

    public function autoCompleteProduto()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->vendas_model->autoCompleteProduto($q);
        }
    }

    public function autoCompleteCliente()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->vendas_model->autoCompleteCliente($q);
        }
    }

    public function autoCompleteUsuario()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->vendas_model->autoCompleteUsuario($q);
        }
    }

    public function adicionarProduto()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar vendas.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('quantidade', 'Quantidade', 'trim|required');
        $this->form_validation->set_rules('idProduto', 'Produto', 'trim|required');
        $this->form_validation->set_rules('idVendasProduto', 'Vendas', 'trim|required');

        $idVenda = $this->input->post('idVendasProduto');
        $venda = $this->vendas_model->getById($idVenda);
        $editavel = $this->vendas_model->isEditable($idVenda);

        if (! $venda) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode(['result' => false, 'messages' => '<br /><br /> <strong>Motivo:</strong> Venda não encontrada']));
        }
        if (!$editavel) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode(['result' => false, 'messages' => '<br /><br /> <strong>Motivo:</strong> Venda já faturada']));
        }

        if ($this->form_validation->run() == false) {
            echo json_encode(['result' => false]);
        } else {
            $preco = $this->input->post('preco');
            $quantidade = $this->input->post('quantidade');
            $subtotal = $preco * $quantidade;
            $produto = $this->input->post('idProduto');
            $data = [
                'quantidade' => $quantidade,
                'subTotal' => $subtotal,
                'produtos_id' => $produto,
                'preco' => $preco,
                'vendas_id' => $idVenda,
            ];

            if ($this->vendas_model->add('itens_de_vendas', $data) == true) {
                $this->load->model('produtos_model');

                // O estoque só é movimentado a partir do faturamento (Receita).
                if ($this->data['configuration']['control_estoque'] && (int) $venda->faturado === 1) {
                    $this->produtos_model->updateEstoque($produto, $quantidade, '-');
                }

                // Atualiza o desconto da venda
                $this->db->set('desconto', 0.00);
                $this->db->set('valor_desconto', 0.00);
                $this->db->set('tipo_desconto', null);
                $this->db->where('idVendas', $idVenda);
                $this->db->update('vendas');

                // Registra a ação nos logs com o ID da venda
                log_info('Adicionou produto à venda com ID: ' . $idVenda);

                echo json_encode(['result' => true]);
            } else {
                echo json_encode(['result' => false]);
            }
        }
    }

    public function excluirProduto()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar Vendas.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('idProduto', 'Produto', 'trim|required');
        $this->form_validation->set_rules('idVendas', 'Venda', 'trim|required');
        $this->form_validation->set_rules('quantidade', 'Quantidade', 'trim|required');
        $this->form_validation->set_rules('produto', 'Produto', 'trim|required');

        if ($this->form_validation->run() == false) {
            echo json_encode(['result' => false, 'messages' => 'Dados inválidos']);
            return;
        }

        $idProduto = $this->input->post('idProduto');
        $idVendas = $this->input->post('idVendas');
        $quantidade = $this->input->post('quantidade');
        $produto = $this->input->post('produto');

        $editavel = $this->vendas_model->isEditable($idVendas);
        if (!$editavel) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode(['result' => false, 'messages' => '<br /><br /> <strong>Motivo:</strong> Venda já faturada']));
        }

        $venda = $this->vendas_model->getById($idVendas);

        $this->db->trans_start();

        $this->vendas_model->delete('itens_de_vendas', 'idItens', $idProduto);

        // Devolve ao estoque apenas o que foi baixado no faturamento.
        if ($this->data['configuration']['control_estoque'] && $venda && (int) $venda->faturado === 1) {
            $this->load->model('produtos_model');
            $this->produtos_model->updateEstoque($produto, $quantidade, '+');
        }

        $this->db->set('desconto', 0.00);
        $this->db->set('valor_desconto', 0.00);
        $this->db->set('tipo_desconto', null);
        $this->db->where('idVendas', $idVendas);
        $this->db->update('vendas');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['result' => false, 'messages' => 'Erro ao excluir o produto']);
        } else {
            $this->db->trans_complete();
            log_info('Removeu produto da venda. ID da Venda: ' . $idVendas . ', ID do Produto: ' . $idProduto);
            echo json_encode(['result' => true, 'messages' => 'Produto removido com sucesso']);
        }
    }

    public function adicionarDesconto()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar Vendas.');
            redirect(base_url());
        }

        if ($this->input->post('desconto') == '') {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['messages' => 'Campo desconto vazio']));
        } else {
            $idVendas = $this->input->post('idVendas');
            $data = [
                'desconto' => $this->input->post('desconto'),
                'tipo_desconto' => $this->input->post('tipoDesconto'),
                'valor_desconto' => $this->input->post('resultado'),
            ];
            $editavel = $this->vendas_model->isEditable($idVendas);
            if (! $editavel) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['result' => false, 'messages', 'Desconto não pode ser adiciona. Venda não ja Faturada/Cancelada']));
            }
            if ($this->vendas_model->edit('vendas', $data, 'idVendas', $idVendas) == true) {
                log_info('Adicionou um desconto na Venda. ID: ' . $idVendas);

                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(201)
                    ->set_output(json_encode(['result' => true, 'messages' => 'Desconto adicionado com sucesso!']));
            } else {
                log_info('Ocorreu um erro ao tentar adiciona desconto a Venda: ' . $idVendas);

                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['result' => false, 'messages', 'Ocorreu um erro ao tentar adiciona desconto a Venda.']));
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode(['result' => false, 'messages', 'Ocorreu um erro ao tentar adiciona desconto a OS.']));
    }

    public function faturar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar Vendas');
            redirect(base_url());
        }

        $this->load->library('form_validation');

        if ($this->form_validation->run('receita') == false) {
            echo json_encode([
                'result' => false,
                'message' => strip_tags(validation_errors()) ?: 'Verifique os campos do faturamento.',
            ]);
            exit();
        }

        $vencimento = $this->converterData($this->input->post('vencimento'));
        $recebimento = (string) $this->input->post('recebimento');
        $recebimento = $recebimento !== '' ? $this->converterData($recebimento) : null;

        if ($vencimento === null) {
            echo json_encode(['result' => false, 'message' => 'Data de entrada inválida.']);
            exit();
        }

        if ((string) $this->input->post('recebimento') !== '' && $recebimento === null) {
            echo json_encode(['result' => false, 'message' => 'Data de recebimento inválida.']);
            exit();
        }

        $resultado = $this->vendas_model->faturarVenda($this->input->post('vendas_id'), [
            'vencimento' => $vencimento,
            'recebimento' => $recebimento,
            'recebido' => $this->input->post('recebido') == 1,
            'formaPgto' => $this->input->post('formaPgto'),
            'descricao' => $this->input->post('descricao'),
            'usuarios_id' => $this->session->userdata('id_admin'),
        ]);

        echo json_encode($resultado);
        exit();
    }

    private function converterData($data)
    {
        $data = DateTime::createFromFormat('d/m/Y', (string) $data);

        return $data instanceof DateTime ? $data->format('Y-m-d') : null;
    }

    public function atualizarStatusEmMassa()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            echo json_encode(['result' => false, 'message' => 'Você não tem permissão para editar Vendas.']);
            exit();
        }

        $ids = $this->input->post('ids');
        $status = $this->input->post('status');
        $filtroStatus = $this->input->post('filtroStatus');

        if (empty($ids) || ! is_array($ids) || empty($status)) {
            echo json_encode(['result' => false, 'message' => 'Nenhuma venda selecionada ou status inválido.']);
            exit();
        }

        $allowedStatus = $this->vendas_model->statusComerciais();
        if (! in_array($status, $allowedStatus, true)) {
            echo json_encode(['result' => false, 'message' => 'Status inválido.']);
            exit();
        }

        if ($status === 'Faturado') {
            echo json_encode(['result' => false, 'message' => 'Para faturar, selecione as vendas e use o faturamento em massa.']);
            exit();
        }

        $this->db->trans_start();

        $updated = 0;
        $skipped = [];

        foreach ($ids as $id) {
            $venda = $this->vendas_model->getById($id);
            if (! $venda) {
                $skipped[] = "Venda #{$id} (não encontrada)";
                continue;
            }

            $oldStatus = $venda->status;

            if (! $this->vendas_model->isEditable($id)) {
                $skipped[] = "Venda #{$id} ({$oldStatus} — não editável)";
                continue;
            }

            if (! empty($filtroStatus) && $oldStatus !== $filtroStatus) {
                $skipped[] = "Venda #{$id} ({$oldStatus} — não confere com filtro)";
                continue;
            }

            if ((int) $venda->faturado === 1 && $status !== 'Cancelado') {
                $skipped[] = "Venda #{$id} (Faturada — somente pode ser cancelada)";
                continue;
            }

            if ($status === 'Cancelado' && $oldStatus !== 'Cancelado') {
                $this->vendas_model->estornarFaturamento($id);
            }

            $this->vendas_model->edit('vendas', ['status' => $status], 'idVendas', $id);
            $updated++;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            echo json_encode(['result' => false, 'message' => 'Erro ao alterar status em massa. Operação revertida.']);
            exit();
        }

        log_info("Alterou status em massa (vendas): {$updated} vendas para {$status}");

        $message = "{$updated} venda(s) alterada(s) para \"{$status}\".";
        if (! empty($skipped)) {
            $message .= " Pulada(s): " . implode(', ', $skipped);
        }

        echo json_encode(['result' => true, 'updated' => $updated, 'skipped' => $skipped, 'message' => $message]);
        exit();
    }

    public function faturarEmMassa()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            echo json_encode(['result' => false, 'message' => 'Você não tem permissão para editar Vendas.']);
            exit();
        }

        $ids = $this->input->post('ids');
        $vencimento = (string) $this->input->post('vencimento');
        $recebimento = (string) $this->input->post('recebimento');
        $recebido = $this->input->post('recebido') ?: 0;

        if (empty($ids) || ! is_array($ids) || $vencimento === '') {
            echo json_encode(['result' => false, 'message' => 'Nenhuma venda selecionada ou data de entrada obrigatória.']);
            exit();
        }

        $vencimentoData = $this->converterData($vencimento);
        $recebimentoData = $recebimento !== '' ? $this->converterData($recebimento) : null;

        if ($vencimentoData === null) {
            echo json_encode(['result' => false, 'message' => 'Data de entrada inválida.']);
            exit();
        }

        if ($recebimento !== '' && $recebimentoData === null) {
            echo json_encode(['result' => false, 'message' => 'Data de recebimento inválida.']);
            exit();
        }

        $faturados = 0;
        $skipped = [];

        foreach ($ids as $id) {
            $resultado = $this->vendas_model->faturarVenda($id, [
                'vencimento' => $vencimentoData,
                'recebimento' => $recebimentoData,
                'recebido' => $recebido,
                'formaPgto' => $this->input->post('formaPgto'),
                'usuarios_id' => $this->session->userdata('id_admin'),
            ]);

            if ($resultado['result']) {
                $faturados++;
                log_info('Faturou venda em massa. ID: ' . $id);
            } else {
                $skipped[] = "Venda #{$id} ({$resultado['message']})";
            }
        }

        log_info("Faturou vendas em massa: {$faturados} vendas");

        $message = "{$faturados} venda(s) faturada(s) com sucesso.";
        if (! empty($skipped)) {
            $message .= ' Pulada(s): ' . implode(', ', $skipped);
        }

        echo json_encode(['result' => true, 'updated' => $faturados, 'skipped' => $skipped, 'message' => $message]);
        exit();
    }

    public function validarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }
        $soma1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma1 += $cpf[$i] * (10 - $i);
        }
        $resto1 = $soma1 % 11;
        $dv1 = ($resto1 < 2) ? 0 : 11 - $resto1;
        if ($dv1 != $cpf[9]) {
            return false;
        }
        $soma2 = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma2 += $cpf[$i] * (11 - $i);
        }
        $resto2 = $soma2 % 11;
        $dv2 = ($resto2 < 2) ? 0 : 11 - $resto2;
    
        return $dv2 == $cpf[10];
    }
    
    public function validarCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1+$/', $cnpj)) {
            return false;
        }
        $soma1 = 0;
        for ($i = 0, $pos = 5; $i < 12; $i++, $pos--) {
            $pos = ($pos < 2) ? 9 : $pos;
            $soma1 += $cnpj[$i] * $pos;
        }
        $dv1 = ($soma1 % 11 < 2) ? 0 : 11 - ($soma1 % 11);
        if ($dv1 != $cnpj[12]) {
            return false;
        }
        $soma2 = 0;
        for ($i = 0, $pos = 6; $i < 13; $i++, $pos--) {
            $pos = ($pos < 2) ? 9 : $pos;
            $soma2 += $cnpj[$i] * $pos;
        }
        $dv2 = ($soma2 % 11 < 2) ? 0 : 11 - ($soma2 % 11);
    
        return $dv2 == $cnpj[13];
    }
    
    public function formatarChave($chave)
    {
        if ($this->validarCPF($chave)) {
            return substr($chave, 0, 3) . '.' . substr($chave, 3, 3) . '.' . substr($chave, 6, 3) . '-' . substr($chave, 9);
        } elseif ($this->validarCNPJ($chave)) {
            return substr($chave, 0, 2) . '.' . substr($chave, 2, 3) . '.' . substr($chave, 5, 3) . '/' . substr($chave, 8, 4) . '-' . substr($chave, 12);
        } elseif (strlen($chave) === 11) {
            return '(' . substr($chave, 0, 2) . ') ' . substr($chave, 2, 5) . '-' . substr($chave, 7);
        }
        return $chave;
    }

    public function visualizarVenda($id)
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar Vendas.');
            redirect(base_url());
        }

        $venda = $this->Vendas_model->getById($id);
        $produtos = $this->Vendas_model->getProdutos($id);
        $total = $this->Vendas_model->getTotalVendas($id);
        
        $data['venda'] = $venda;
        $data['produtos'] = $produtos;
        $data['total'] = $total;

        $this->load->view('vendas/vendas', $data);
    }

}
