<?php

class TestData extends Seeder
{
    public function run()
    {
        echo 'Running TestData Seeder...' . PHP_EOL;

        $this->faker = Faker\Factory::create('pt_BR');
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        $this->seedEmitente();
        $this->seedClientes();
        $this->seedCategorias();
        $this->seedContas();
        $this->seedMarcas();
        $this->seedServicos();
        $this->seedProdutos();
        $this->seedGarantias();
        $this->seedEquipamentos();
        $this->seedOs();
        $this->seedProdutosOs();
        $this->seedServicosOs();
        $this->seedEquipamentosOs();
        $this->seedAnotacoesOs();
        $this->seedVendas();
        $this->seedItensDeVendas();
        $this->seedLancamentos();
        $this->seedLogs();

        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        echo 'TestData completed!' . PHP_EOL;
    }

    private function seedEmitente()
    {
        $this->db->truncate('emitente');
        $this->db->insert('emitente', [
            'nome' => 'Oficina do João Ltda',
            'cnpj' => '11.222.333/0001-44',
            'ie' => '123.456.789.000',
            'rua' => 'Av. Paulista',
            'numero' => '1000',
            'bairro' => 'Bela Vista',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'telefone' => '(11) 3000-0000',
            'email' => 'contato@oficinadjoao.com.br',
            'cep' => '01310-100',
        ]);
        echo '  Emitente criado.' . PHP_EOL;
    }

    private function seedClientes()
    {
        $this->db->truncate('clientes');

        $clientesFixos = [
            [
                'nomeCliente' => 'Maria Silva',
                'sexo' => 'Feminino',
                'documento' => '123.456.789-01',
                'telefone' => '(11) 99999-0001',
                'celular' => '(11) 98888-0001',
                'email' => 'maria.silva@email.com',
                'rua' => 'Rua Augusta',
                'numero' => '500',
                'bairro' => 'Consolação',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01304-001',
            ],
            [
                'nomeCliente' => 'Carlos Santos',
                'sexo' => 'Masculino',
                'documento' => '987.654.321-00',
                'telefone' => '(11) 99999-0002',
                'celular' => '(11) 98888-0002',
                'email' => 'carlos.santos@email.com',
                'rua' => 'Rua Oscar Freire',
                'numero' => '200',
                'bairro' => 'Jardins',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01426-001',
            ],
            [
                'nomeCliente' => 'Ana Oliveira ME',
                'sexo' => 'Feminino',
                'pessoa_fisica' => 0,
                'documento' => '11.222.333/0001-44',
                'telefone' => '(11) 99999-0003',
                'celular' => '(11) 98888-0003',
                'email' => 'ana.oliveira@empresa.com',
                'rua' => 'Av. Brigadeiro Faria Lima',
                'numero' => '1500',
                'bairro' => 'Pinheiros',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01452-001',
            ],
        ];

        foreach ($clientesFixos as $c) {
            $this->db->insert('clientes', array_merge([
                'pessoa_fisica' => 1,
                'senha' => password_hash('123', PASSWORD_DEFAULT),
                'dataCadastro' => $this->faker->dateTimeBetween('-6 months', '-1 month')->format('Y-m-d'),
                'fornecedor' => 0,
                'complemento' => null,
                'contato' => null,
            ], $c));
        }

        for ($i = 0; $i < 7; $i++) {
            $this->db->insert('clientes', [
                'nomeCliente' => $this->faker->name,
                'sexo' => $this->faker->randomElement(['Masculino', 'Feminino']),
                'pessoa_fisica' => 1,
                'documento' => $this->faker->cpf,
                'telefone' => $this->faker->phoneNumber,
                'celular' => $this->faker->phoneNumber,
                'email' => $this->faker->email,
                'senha' => password_hash('123', PASSWORD_DEFAULT),
                'dataCadastro' => $this->faker->dateTimeBetween('-6 months', '-1 month')->format('Y-m-d'),
                'rua' => $this->faker->streetName,
                'numero' => (string) $this->faker->buildingNumber,
                'bairro' => $this->faker->city,
                'cidade' => $this->faker->city,
                'estado' => $this->faker->stateAbbr,
                'cep' => $this->faker->postcode,
                'fornecedor' => 0,
            ]);
        }

        echo '  10 clientes criados.' . PHP_EOL;
    }

    private function seedCategorias()
    {
        $this->db->truncate('categorias');
        $cats = [
            ['categoria' => 'Manutenção', 'tipo' => 'serviço'],
            ['categoria' => 'Conserto', 'tipo' => 'serviço'],
            ['categoria' => 'Peças', 'tipo' => 'produto'],
            ['categoria' => 'Acessórios', 'tipo' => 'produto'],
            ['categoria' => 'Receita', 'tipo' => 'receita'],
            ['categoria' => 'Despesa', 'tipo' => 'despesa'],
            ['categoria' => 'Aluguel', 'tipo' => 'despesa'],
        ];
        foreach ($cats as $c) {
            $this->db->insert('categorias', [
                'categoria' => $c['categoria'],
                'cadastro' => date('Y-m-d'),
                'status' => 1,
                'tipo' => $c['tipo'],
            ]);
        }
        echo '  Categorias criadas.' . PHP_EOL;
    }

    private function seedContas()
    {
        $this->db->truncate('contas');
        $contas = [
            ['conta' => 'Caixa', 'banco' => 'Dinheiro', 'saldo' => 5000.00],
            ['conta' => 'Conta Corrente', 'banco' => 'Banco do Brasil', 'saldo' => 15000.00],
            ['conta' => 'Poupança', 'banco' => 'Caixa Econômica', 'saldo' => 25000.00],
        ];
        foreach ($contas as $c) {
            $this->db->insert('contas', [
                'conta' => $c['conta'],
                'banco' => $c['banco'],
                'numero' => (string) $this->faker->bankAccountNumber,
                'saldo' => $c['saldo'],
                'cadastro' => date('Y-m-d'),
                'status' => 1,
                'tipo' => 'Conta Corrente',
            ]);
        }
        echo '  Contas criadas.' . PHP_EOL;
    }

    private function seedMarcas()
    {
        $this->db->truncate('marcas');
        $marcas = ['Samsung', 'LG', 'Sony', 'Panasonic', 'Philips', 'Dell', 'HP', 'Lenovo', 'Apple', 'Positivo', 'Multilaser', 'Eletrolux', 'Brastemp', 'Consul'];
        foreach ($marcas as $m) {
            $this->db->insert('marcas', [
                'marca' => $m,
                'cadastro' => date('Y-m-d'),
                'situacao' => 1,
            ]);
        }
        echo '  Marcas criadas.' . PHP_EOL;
    }

    private function seedServicos()
    {
        $this->db->truncate('servicos');
        $servicos = [
            ['nome' => 'Formatação de Computador', 'descricao' => 'Formatação completa com instalação de SO', 'preco' => 150.00],
            ['nome' => 'Troca de Tela Smartphone', 'descricao' => 'Substituição de tela quebrada', 'preco' => 350.00],
            ['nome' => 'Limpeza Interna Notebook', 'descricao' => 'Limpeza de cooler e pasta térmica', 'preco' => 120.00],
            ['nome' => 'Instalação de Software', 'descricao' => 'Instalação de programas diversos', 'preco' => 80.00],
            ['nome' => 'Reparo Fonte Notebook', 'descricao' => 'Conserto ou substituição de fonte', 'preco' => 200.00],
            ['nome' => 'Configuração de Rede', 'descricao' => 'Configuração de roteador e rede Wi-Fi', 'preco' => 100.00],
            ['nome' => 'Manutenção Preventiva', 'descricao' => 'Revisão geral do equipamento', 'preco' => 180.00],
            ['nome' => 'Backup de Dados', 'descricao' => 'Cópia de segurança de arquivos', 'preco' => 90.00],
        ];
        foreach ($servicos as $s) {
            $this->db->insert('servicos', $s);
        }
        echo '  Serviços criados.' . PHP_EOL;
    }

    private function seedProdutos()
    {
        $this->db->truncate('produtos');
        $produtos = [
            ['descricao' => 'Fonte ATX 500W', 'unidade' => 'UN', 'precoCompra' => 80.00, 'precoVenda' => 159.90, 'estoque' => 15, 'estoqueMinimo' => 3],
            ['descricao' => 'Teclado USB Padrão ABNT2', 'unidade' => 'UN', 'precoCompra' => 25.00, 'precoVenda' => 59.90, 'estoque' => 30, 'estoqueMinimo' => 5],
            ['descricao' => 'Mouse Óptico USB', 'unidade' => 'UN', 'precoCompra' => 15.00, 'precoVenda' => 39.90, 'estoque' => 40, 'estoqueMinimo' => 10],
            ['descricao' => 'Cabo HDMI 1.5m', 'unidade' => 'UN', 'precoCompra' => 8.00, 'precoVenda' => 24.90, 'estoque' => 50, 'estoqueMinimo' => 10],
            ['descricao' => 'Pendrive 32GB', 'unidade' => 'UN', 'precoCompra' => 22.00, 'precoVenda' => 54.90, 'estoque' => 25, 'estoqueMinimo' => 5],
            ['descricao' => 'HD Externo 1TB', 'unidade' => 'UN', 'precoCompra' => 180.00, 'precoVenda' => 349.90, 'estoque' => 8, 'estoqueMinimo' => 2],
            ['descricao' => 'Memória RAM 8GB DDR4', 'unidade' => 'UN', 'precoCompra' => 95.00, 'precoVenda' => 189.90, 'estoque' => 12, 'estoqueMinimo' => 3],
            ['descricao' => 'SSD 240GB', 'unidade' => 'UN', 'precoCompra' => 120.00, 'precoVenda' => 249.90, 'estoque' => 10, 'estoqueMinimo' => 2],
            ['descricao' => 'Película de Vidro Smartphone', 'unidade' => 'UN', 'precoCompra' => 3.50, 'precoVenda' => 19.90, 'estoque' => 60, 'estoqueMinimo' => 20],
            ['descricao' => 'Carregador Universal USB', 'unidade' => 'UN', 'precoCompra' => 10.00, 'precoVenda' => 34.90, 'estoque' => 20, 'estoqueMinimo' => 5],
        ];
        foreach ($produtos as $p) {
            $this->db->insert('produtos', [
                'codDeBarra' => (string) $this->faker->ean13,
                'descricao' => $p['descricao'],
                'unidade' => $p['unidade'],
                'precoCompra' => $p['precoCompra'],
                'precoVenda' => $p['precoVenda'],
                'estoque' => $p['estoque'],
                'estoqueMinimo' => $p['estoqueMinimo'],
                'saida' => null,
                'entrada' => null,
            ]);
        }
        echo '  Produtos criados.' . PHP_EOL;
    }

    private function seedGarantias()
    {
        $this->db->truncate('garantias');
        $garantias = [
            ['refGarantia' => 'GAR001', 'textoGarantia' => 'Garantia de 90 dias contra defeitos de fabricação.'],
            ['refGarantia' => 'GAR002', 'textoGarantia' => 'Garantia de 6 meses para serviços de mão de obra.'],
            ['refGarantia' => 'GAR003', 'textoGarantia' => 'Garantia de 1 ano para peças substituídas.'],
        ];
        foreach ($garantias as $g) {
            $this->db->insert('garantias', [
                'dataGarantia' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                'refGarantia' => $g['refGarantia'],
                'textoGarantia' => $g['textoGarantia'],
                'usuarios_id' => 1,
            ]);
        }
        echo '  Garantias criadas.' . PHP_EOL;
    }

    private function seedEquipamentos()
    {
        $this->db->truncate('equipamentos');
        $equipamentos = [
            ['equipamento' => 'Notebook Dell Inspiron', 'num_serie' => 'DEL123456', 'clientes_id' => 1],
            ['equipamento' => 'Smartphone Samsung Galaxy S24', 'num_serie' => 'SAM789012', 'clientes_id' => 2],
            ['equipamento' => 'PC Desktop', 'num_serie' => 'PC345678', 'clientes_id' => 3],
            ['equipamento' => 'Monitor LG 27"', 'num_serie' => 'LG901234', 'clientes_id' => 1],
            ['equipamento' => 'Notebook Lenovo ThinkPad', 'num_serie' => 'LEN567890', 'clientes_id' => 4],
            ['equipamento' => 'iPhone 15 Pro', 'num_serie' => 'AP123789', 'clientes_id' => 5],
            ['equipamento' => 'Impressora HP LaserJet', 'num_serie' => 'HP456123', 'clientes_id' => 3],
        ];
        foreach ($equipamentos as $e) {
            $this->db->insert('equipamentos', [
                'equipamento' => $e['equipamento'],
                'num_serie' => $e['num_serie'],
                'modelo' => $this->faker->bothify('Modelo-####'),
                'cor' => $this->faker->randomElement(['Preto', 'Branco', 'Prata', 'Azul']),
                'descricao' => $this->faker->sentence(3),
                'marcas_id' => $this->faker->numberBetween(1, 14),
                'clientes_id' => $e['clientes_id'],
            ]);
        }
        echo '  Equipamentos criados.' . PHP_EOL;
    }

    private function seedOs()
    {
        $this->db->truncate('os');
        $this->db->truncate('produtos_os');
        $this->db->truncate('servicos_os');
        $this->db->truncate('equipamentos_os');
        $this->db->truncate('anotacoes_os');
        $this->db->truncate('anexos');

        $statusList = ['Aberto', 'Em Andamento', 'Aguardando Peças', 'Orçamento', 'Aprovado', 'Finalizado'];

        $osData = [
            ['cliente' => 1, 'equip' => 1, 'defeito' => 'Ligando mas não dá vídeo', 'status' => 'Finalizado'],
            ['cliente' => 2, 'equip' => 2, 'defeito' => 'Tela trincada e toque não funciona', 'status' => 'Aguardando Peças'],
            ['cliente' => 3, 'equip' => 3, 'defeito' => 'Superaquecimento e desligamento inesperado', 'status' => 'Em Andamento'],
            ['cliente' => 1, 'equip' => 4, 'defeito' => 'Monitor piscando', 'status' => 'Aberto'],
            ['cliente' => 4, 'equip' => 5, 'defeito' => 'Teclado com falha em várias teclas', 'status' => 'Orçamento'],
            ['cliente' => 5, 'equip' => 6, 'defeito' => 'Bateria descarregando muito rápido', 'status' => 'Aprovado'],
            ['cliente' => 3, 'equip' => 7, 'defeito' => 'Folha saindo com manchas', 'status' => 'Finalizado'],
        ];

        foreach ($osData as $o) {
            $dataInicial = $this->faker->dateTimeBetween('-3 months', '-1 week');
            $dataFinal = $o['status'] === 'Finalizado'
                ? $this->faker->dateTimeBetween($dataInicial, 'now')->format('Y-m-d')
                : null;

            $this->db->insert('os', [
                'dataInicial' => $dataInicial->format('Y-m-d'),
                'dataFinal' => $dataFinal,
                'garantia' => $this->faker->randomElement(['90 dias', '6 meses', null]),
                'descricaoProduto' => $this->faker->sentence(6),
                'defeito' => $o['defeito'],
                'status' => $o['status'],
                'observacoes' => $this->faker->optional(0.7)->sentence(8),
                'laudoTecnico' => $o['status'] === 'Finalizado' ? $this->faker->sentence(10) : null,
                'valorTotal' => $this->faker->randomFloat(2, 100, 2000),
                'clientes_id' => $o['cliente'],
                'usuarios_id' => 1,
                'faturado' => $o['status'] === 'Finalizado' ? 1 : 0,
            ]);
        }

        echo '  Ordens de Serviço criadas.' . PHP_EOL;
    }

    private function seedProdutosOs()
    {
        $osCount = $this->db->count_all('os');
        $prodIds = range(1, 10);

        for ($osId = 1; $osId <= $osCount; $osId++) {
            $numItems = $this->faker->numberBetween(1, 3);
            $usedProds = [];
            for ($i = 0; $i < $numItems; $i++) {
                $prodId = $this->faker->randomElement($prodIds);
                if (in_array($prodId, $usedProds)) {
                    continue;
                }
                $usedProds[] = $prodId;
                $qty = $this->faker->numberBetween(1, 3);
                $unitPrice = $this->faker->randomFloat(2, 15, 350);
                $this->db->insert('produtos_os', [
                    'quantidade' => $qty,
                    'descricao' => $this->faker->randomElement(['Fonte ATX', 'Teclado', 'Mouse', 'Cabo HDMI', 'Pendrive', 'HD Externo', 'Memória RAM', 'SSD']),
                    'preco' => $unitPrice,
                    'os_id' => $osId,
                    'produtos_id' => $prodId,
                    'subTotal' => round($qty * $unitPrice, 2),
                ]);
            }
        }
        echo '  Produtos vinculados às OS.' . PHP_EOL;
    }

    private function seedServicosOs()
    {
        $osCount = $this->db->count_all('os');
        $servIds = range(1, 8);

        for ($osId = 1; $osId <= $osCount; $osId++) {
            $numServ = $this->faker->numberBetween(1, 2);
            for ($i = 0; $i < $numServ; $i++) {
                $servId = $this->faker->randomElement($servIds);
                $qty = $this->faker->numberBetween(1, 2);
                $unitPrice = $this->faker->randomFloat(2, 80, 400);
                $this->db->insert('servicos_os', [
                    'servico' => $this->faker->randomElement(['Formatação', 'Troca de Tela', 'Limpeza', 'Reparo', 'Instalação']),
                    'quantidade' => $qty,
                    'preco' => $unitPrice,
                    'os_id' => $osId,
                    'servicos_id' => $servId,
                    'subTotal' => round($qty * $unitPrice, 2),
                ]);
            }
        }
        echo '  Serviços vinculados às OS.' . PHP_EOL;
    }

    private function seedEquipamentosOs()
    {
        $osCount = $this->db->count_all('os');
        $equipCount = $this->db->count_all('equipamentos');

        for ($osId = 1; $osId <= $osCount && $osId <= $equipCount; $osId++) {
            $this->db->insert('equipamentos_os', [
                'defeito_declarado' => $this->faker->sentence(4),
                'defeito_encontrado' => $this->faker->optional(0.6)->sentence(5),
                'solucao' => $this->faker->optional(0.5)->sentence(4),
                'equipamentos_id' => $osId,
                'os_id' => $osId,
            ]);
        }
        echo '  Equipamentos vinculados às OS.' . PHP_EOL;
    }

    private function seedAnotacoesOs()
    {
        $osCount = $this->db->count_all('os');
        for ($osId = 1; $osId <= $osCount; $osId++) {
            $numNotes = $this->faker->numberBetween(0, 3);
            for ($i = 0; $i < $numNotes; $i++) {
                $this->db->insert('anotacoes_os', [
                    'anotacao' => $this->faker->sentence(6),
                    'data_hora' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d H:i:s'),
                    'os_id' => $osId,
                ]);
            }
        }
        echo '  Anotações criadas.' . PHP_EOL;
    }

    private function seedVendas()
    {
        $this->db->truncate('vendas');
        $this->db->truncate('itens_de_vendas');

        $vendasData = [
            ['cliente' => 2, 'status' => 'Finalizado'],
            ['cliente' => 4, 'status' => 'Finalizado'],
            ['cliente' => 1, 'status' => 'Finalizado'],
            ['cliente' => 6, 'status' => 'Aberto'],
            ['cliente' => 3, 'status' => 'Finalizado'],
        ];

        foreach ($vendasData as $v) {
            $this->db->insert('vendas', [
                'dataVenda' => $this->faker->dateTimeBetween('-2 months', '-1 week')->format('Y-m-d'),
                'valorTotal' => $this->faker->randomFloat(2, 200, 3000),
                'clientes_id' => $v['cliente'],
                'usuarios_id' => 1,
                'faturado' => $v['status'] === 'Finalizado' ? 1 : 0,
                'status' => $v['status'],
                'observacoes' => $this->faker->optional(0.5)->sentence(6),
            ]);
        }
        echo '  Vendas criadas.' . PHP_EOL;
    }

    private function seedItensDeVendas()
    {
        $vendaCount = $this->db->count_all('vendas');
        $prodIds = range(1, 10);

        for ($vendaId = 1; $vendaId <= $vendaCount; $vendaId++) {
            $numItems = $this->faker->numberBetween(1, 4);
            for ($i = 0; $i < $numItems; $i++) {
                $prodId = $this->faker->randomElement($prodIds);
                $qty = $this->faker->numberBetween(1, 3);
                $price = $this->faker->randomFloat(2, 25, 500);
                $this->db->insert('itens_de_vendas', [
                    'subTotal' => round($qty * $price, 2),
                    'quantidade' => $qty,
                    'preco' => $price,
                    'vendas_id' => $vendaId,
                    'produtos_id' => $prodId,
                ]);
            }
        }
        echo '  Itens de vendas criados.' . PHP_EOL;
    }

    private function seedLancamentos()
    {
        $this->db->truncate('lancamentos');

        $receitas = [
            ['descricao' => 'Pagamento OS #1', 'valor' => 850.00, 'cliente' => 1, 'tipo' => 'receita'],
            ['descricao' => 'Pagamento OS #7', 'valor' => 1200.00, 'cliente' => 3, 'tipo' => 'receita'],
            ['descricao' => 'Venda #1', 'valor' => 650.00, 'cliente' => 2, 'tipo' => 'receita'],
        ];
        $despesas = [
            ['descricao' => 'Aluguel do mês', 'valor' => 2500.00, 'cliente' => null, 'tipo' => 'despesa'],
            ['descricao' => 'Compra de peças', 'valor' => 1800.00, 'cliente' => null, 'tipo' => 'despesa'],
            ['descricao' => 'Conta de Luz', 'valor' => 450.00, 'cliente' => null, 'tipo' => 'despesa'],
        ];

        foreach ($receitas as $r) {
            $vencimento = $this->faker->dateTimeBetween('-2 months', '-1 week');
            $this->db->insert('lancamentos', [
                'descricao' => $r['descricao'],
                'valor' => $r['valor'],
                'data_vencimento' => $vencimento->format('Y-m-d'),
                'data_pagamento' => $vencimento->modify('+3 days')->format('Y-m-d'),
                'baixado' => 1,
                'tipo' => $r['tipo'],
                'clientes_id' => $r['cliente'],
                'categorias_id' => 5,
                'contas_id' => 2,
                'usuarios_id' => 1,
            ]);
        }

        foreach ($despesas as $d) {
            $vencimento = $this->faker->dateTimeBetween('-1 month', 'now');
            $this->db->insert('lancamentos', [
                'descricao' => $d['descricao'],
                'valor' => $d['valor'],
                'data_vencimento' => $vencimento->format('Y-m-d'),
                'data_pagamento' => $vencimento->format('Y-m-d'),
                'baixado' => 0,
                'tipo' => $d['tipo'],
                'categorias_id' => 6,
                'contas_id' => 1,
                'usuarios_id' => 1,
            ]);
        }

        echo '  Lançamentos criados.' . PHP_EOL;
    }

    private function seedLogs()
    {
        $this->db->truncate('logs');
        for ($i = 0; $i < 15; $i++) {
            $this->db->insert('logs', [
                'usuario' => 'Admin',
                'tarefa' => $this->faker->randomElement([
                    'Cadastrou um cliente',
                    'Editou uma OS',
                    'Cadastrou um produto',
                    'Realizou uma venda',
                    'Gerou relatório financeiro',
                    'Alterou status da OS',
                    'Fez login no sistema',
                    'Cadastrou um serviço',
                ]),
                'data' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
                'hora' => $this->faker->time('H:i:s'),
                'ip' => $this->faker->localIpv4,
            ]);
        }
        echo '  Logs criados.' . PHP_EOL;
    }
}
