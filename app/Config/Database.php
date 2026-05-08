<?php

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

        // DEBUG TEMPORÁRIO
        var_dump($_ENV);
        exit;

        // Dados de conexão vindos do .env
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '5432';
        $dbName = $_ENV['DB_NAME'] ?? 'agenda';
        $user = $_ENV['DB_USER'] ?? 'postgres';
        $pass = $_ENV['DB_PASS'] ?? '';

        // Monta o endereço de conexão do PostgreSQL
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
            $host,
            $port,
            $dbName
        );

        try {
            // Cria a conexão PDO com o banco
            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException(
                'Erro ao conectar no banco: ' . $e->getMessage()
            );
        }

        return self::$connection;
    }
}