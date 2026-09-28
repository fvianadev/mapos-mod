<?php

class Migration_add_permissao_status_massa extends CI_Migration
{
    private $novasChaves = [
        'sOs' => 'eOs',
        'sVenda' => 'eVenda',
    ];

    public function up()
    {
        foreach ($this->db->get('permissoes')->result() as $perfil) {
            $permissoes = $this->decodificar($perfil->permissoes);
            if (! is_array($permissoes)) {
                continue;
            }

            $alterado = false;
            foreach ($this->novasChaves as $nova => $origem) {
                if (! array_key_exists($nova, $permissoes)) {
                    $permissoes[$nova] = array_key_exists($origem, $permissoes) ? $permissoes[$origem] : '0';
                    $alterado = true;
                }
            }

            if ($alterado) {
                $this->db->where('idPermissao', $perfil->idPermissao)
                    ->update('permissoes', ['permissoes' => $this->codificar($permissoes, $perfil->permissoes)]);
            }
        }
    }

    public function down()
    {
        foreach ($this->db->get('permissoes')->result() as $perfil) {
            $permissoes = $this->decodificar($perfil->permissoes);
            if (! is_array($permissoes)) {
                continue;
            }

            $removido = false;
            foreach (array_keys($this->novasChaves) as $chave) {
                if (array_key_exists($chave, $permissoes)) {
                    unset($permissoes[$chave]);
                    $removido = true;
                }
            }

            if ($removido) {
                $this->db->where('idPermissao', $perfil->idPermissao)
                    ->update('permissoes', ['permissoes' => $this->codificar($permissoes, $perfil->permissoes)]);
            }
        }
    }

    private function decodificar($valor)
    {
        if (! is_string($valor) || $valor === '') {
            return null;
        }

        $json = json_decode($valor, true);
        if (is_array($json)) {
            return $json;
        }

        $serializado = @unserialize($valor, ['allowed_classes' => false]);

        return is_array($serializado) ? $serializado : null;
    }

    private function codificar(array $permissoes, $original)
    {
        return is_array(json_decode($original, true)) ? json_encode($permissoes) : serialize($permissoes);
    }
}
