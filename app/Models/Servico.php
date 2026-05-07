<?php

/*

Model responsável por acessar a tabela de serviços.
Ele lista, busca, cria, atualiza e exclui serviços.

*/

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Servico
{
    // Guarda a conexão com o banco
    private PDO $conn;

    // Ao criar o model, pega a conexão com o banco
    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // Lista todos os serviços
    public function findAll(): array
    {
        // Executa a consulta no banco
        $stmt = $this->conn->query('SELECT * FROM servicos ORDER BY id DESC');

        // Retorna todos os serviços encontrados
        return $stmt->fetchAll();
    }

    // Busca um serviço pelo ID
    public function findById(int $id): ?array
    {
        // Prepara a consulta
        $stmt = $this->conn->prepare('SELECT * FROM servicos WHERE id = :id');

        // Executa passando o ID
        $stmt->execute([':id' => $id]);

        // Pega o resultado
        $row = $stmt->fetch();

        // Retorna o serviço ou null
        return $row ?: null;
    }

    // Cria um novo serviço
    public function create(array $data): int
    {
        // Prepara o INSERT do serviço
        $stmt = $this->conn->prepare(
            'INSERT INTO servicos (nome, descricao, preco)
             VALUES (:nome, :descricao, :preco)
             RETURNING id'
        );

        // Executa o INSERT com os dados recebidos
        $stmt->execute([
            ':nome' => trim($data['nome']),
            ':descricao' => $data['descricao'] ?? null,
            ':preco' => $data['preco'],
        ]);

        // Retorna o ID do serviço criado
        return (int) $stmt->fetchColumn();
    }

    // Atualiza um serviço existente
    public function update(int $id, array $data): bool
    {
        // Prepara o UPDATE do serviço
        $stmt = $this->conn->prepare(
            'UPDATE servicos
             SET nome = :nome, descricao = :descricao, preco = :preco
             WHERE id = :id'
        );

        // Executa a atualização
        return $stmt->execute([
            ':id' => $id,
            ':nome' => trim($data['nome']),
            ':descricao' => $data['descricao'] ?? null,
            ':preco' => $data['preco'],
        ]);
    }

    // Exclui um serviço pelo ID
    public function delete(int $id): bool
    {
        // Prepara o DELETE
        $stmt = $this->conn->prepare('DELETE FROM servicos WHERE id = :id');

        // Executa e retorna true ou false
        return $stmt->execute([':id' => $id]);
    }
}