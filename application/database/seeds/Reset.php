<?php

class Reset extends Seeder
{
    public function run()
    {
        echo 'Resetando banco de dados...' . PHP_EOL;

        $hostname = $_ENV['DB_HOSTNAME'] ?? '127.0.0.1';
        $username = $_ENV['DB_USERNAME'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        $database = $_ENV['DB_DATABASE'] ?? 'mapos';

        $dsn = "mysql:host={$hostname};charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $pdo->exec("DROP DATABASE IF EXISTS `{$database}`");
            echo "  Banco '{$database}' dropado." . PHP_EOL;

            $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            echo "  Banco '{$database}' recriado." . PHP_EOL;

            $pdo->exec("USE `{$database}`");

            $sqlFile = FCPATH . 'banco.sql';
            if (!file_exists($sqlFile)) {
                echo "  ERRO: banco.sql não encontrado em {$sqlFile}" . PHP_EOL;
                return;
            }

            $sql = file_get_contents($sqlFile);
            $pdo->exec($sql);
            echo "  banco.sql executado." . PHP_EOL;

            echo PHP_EOL . 'Reset concluído!' . PHP_EOL;
            echo 'Rodando seeds padrão...' . PHP_EOL;

            $seeds = ['Permissoes', 'Usuarios', 'Configuracoes'];
            foreach ($seeds as $seed) {
                $this->call($seed);
            }

            echo 'Admin: admin@admin.com / senha: 123456' . PHP_EOL;
        } catch (PDOException $e) {
            echo '  ERRO: ' . $e->getMessage() . PHP_EOL;
        }
    }
}
