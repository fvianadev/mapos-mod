<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_ajusta_estoque_vendas_nao_faturadas extends CI_Migration
{
    public function up()
    {
        // Até esta versão a baixa de estoque ocorria já na adição do produto na
        // venda. Com o novo pipeline comercial, o estoque só é baixado no
        // faturamento (Receita). Este ajuste devolve ao estoque o que foi
        // baixado em vendas que ainda não foram faturadas, evitando baixa
        // dupla quando essas vendas forem faturadas.
        if (! $this->controlaEstoque()) {
            return;
        }

        $this->db->query($this->sql('p.estoque = p.estoque + i.quantidade'));
    }

    public function down()
    {
        if (! $this->controlaEstoque()) {
            return;
        }

        $this->db->query($this->sql('p.estoque = p.estoque - i.quantidade'));
    }

    private function controlaEstoque()
    {
        $configuracao = $this->db->get_where('configuracoes', ['config' => 'control_estoque'])->row();

        return $configuracao !== null && (string) $configuracao->valor === '1';
    }

    private function sql($set)
    {
        return "
            UPDATE produtos p
            JOIN itens_de_vendas i ON i.produtos_id = p.idProdutos
            JOIN vendas v ON v.idVendas = i.vendas_id
            SET {$set}
            WHERE v.faturado = 0
        ";
    }
}
