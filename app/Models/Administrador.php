<?php

/*

Model responsável por acessar os dados dos administradores
no banco de dados.

*/

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Administrador
{
    // Guarda a conexão com o banco
    private PDO $conn;

    // Ao criar o model, abre/pega a conexão com o banco
    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // Busca um administrador pelo e-mail
    public function findByEmail(string $email): ?array
    {
        // Prepara a consulta SQL
        $stmt = $this->conn->prepare('SELECT * FROM administradores WHERE email = :email LIMIT 1');

        // Executa a consulta passando o e-mail
        $stmt->execute([':email' => trim($email)]);

        // Pega o resultado encontrado
        $row = $stmt->fetch();

        // Retorna o administrador ou null se não encontrar
        return $row ?: null;
    }

    // Busca um administrador pelo ID
    public function findById(int $id): ?array
    {
        // Prepara a consulta SQL sem retornar a senha
        $stmt = $this->conn->prepare('SELECT id, nome, email FROM administradores WHERE id = :id LIMIT 1');

        // Executa a consulta passando o ID
        $stmt->execute([':id' => $id]);

        // Pega o resultado encontrado
        $row = $stmt->fetch();

        // Retorna o administrador ou null se não encontrar
        return $row ?: null;
    }
}