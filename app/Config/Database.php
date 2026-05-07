<?php

/*

Arquivo responsável pela conexão com o banco de dados.

A conexão é feita usando PDO, uma biblioteca nativa do PHP.
As configurações de conexão vêm do arquivo .env.

*/

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    // Guarda a conexão com o banco
    private static ?PDO $connection = null;

    // Retorna a conexão com o banco
    public static function getConnection(): PDO
    {
        // Se já existir conexão, reutiliza a mesma
        if (self::$connection !== null) {
            return self::$connection;
        }

        // Dados de conexão vindos do .env
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '5432';
        $dbName = $_ENV['DB_NAME'] ?? 'agenda';
        $user = $_ENV['DB_USER'] ?? 'postgres';
        $pass = $_ENV['DB_PASS'] ?? '';

        // Monta o endereço de conexão do PostgreSQL
        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $dbName);

        try {
            // Cria a conexão PDO com o banco
            self::$connection = new PDO($dsn, $user, $pass, [
                // Faz o PDO lançar erro quando algo der errado
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                // Faz as consultas retornarem arrays associativos
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            // Se falhar a conexão, retorna um erro mais claro
            throw new \RuntimeException('Erro ao conectar no banco: ' . $e->getMessage());
        }

        // Retorna a conexão criada
        return self::$connection;
    }
}