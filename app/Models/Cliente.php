<?php

/*

Model responsável por acessar a tabela de clientes
e fazer cadastro, busca, atualização e exclusão.

*/

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Cliente
{
    // Guarda a conexão com o banco
    private PDO $conn;

    // Ao criar o model, pega a conexão com o banco
    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // Cria um novo cliente
    public function create(array $data): int
    {
        // Prepara o INSERT do cliente
        $stmt = $this->conn->prepare(
            'INSERT INTO clientes (nome, email, telefone, senha)
             VALUES (:nome, :email, :telefone, :senha)
             RETURNING id'
        );

        // Executa o INSERT com os dados recebidos
        $stmt->execute([
            ':nome' => trim($data['nome']),
            ':email' => trim($data['email']),
            ':telefone' => $data['telefone'] ?? null,
            ':senha' => password_hash($data['senha'], PASSWORD_DEFAULT),
        ]);

        // Retorna o ID do cliente criado
        return (int) $stmt->fetchColumn();
    }

    // Busca cliente pelo e-mail
    public function findByEmail(string $email): ?array
    {
        // Prepara a consulta
        $stmt = $this->conn->prepare('SELECT * FROM clientes WHERE email = :email LIMIT 1');

        // Executa passando o e-mail
        $stmt->execute([':email' => trim($email)]);

        // Pega o resultado
        $row = $stmt->fetch();

        // Retorna o cliente ou null
        return $row ?: null;
    }

    // Lista todos os clientes
    public function findAll(): array
    {
        // Executa a consulta
        $stmt = $this->conn->query('SELECT id, nome, email, telefone, data_cadastro FROM clientes ORDER BY id DESC');

        // Retorna todos os clientes
        return $stmt->fetchAll();
    }

    // Busca cliente pelo ID, sem retornar a senha
    public function findById(int $id): ?array
    {
        // Prepara a consulta
        $stmt = $this->conn->prepare('SELECT id, nome, email, telefone, data_cadastro FROM clientes WHERE id = :id');

        // Executa passando o ID
        $stmt->execute([':id' => $id]);

        // Pega o resultado
        $row = $stmt->fetch();

        // Retorna o cliente ou null
        return $row ?: null;
    }

    // Busca cliente pelo ID, incluindo a senha
    public function findAuthById(int $id): ?array
    {
        // Prepara a consulta
        $stmt = $this->conn->prepare('SELECT * FROM clientes WHERE id = :id LIMIT 1');

        // Executa passando o ID
        $stmt->execute([':id' => $id]);

        // Pega o resultado
        $row = $stmt->fetch();

        // Retorna o cliente ou null
        return $row ?: null;
    }

    // Atualiza dados do cliente
    public function update(int $id, array $data): bool
    {
        // Lista de campos que serão atualizados
        $fields = [];

        // Parâmetros usados no SQL
        $params = [':id' => $id];

        // Verifica quais campos comuns foram enviados
        foreach (['nome', 'email', 'telefone'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        // Se senha foi enviada, salva a nova senha com hash
        if (array_key_exists('senha', $data) && $data['senha'] !== '') {
            $fields[] = 'senha = :senha';
            $params[':senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }

        // Se nenhum campo foi enviado, não atualiza
        if (!$fields) {
            return false;
        }

        // Monta o UPDATE apenas com os campos enviados
        $sql = 'UPDATE clientes SET ' . implode(', ', $fields) . ' WHERE id = :id';

        // Prepara e executa o UPDATE
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    // Exclui cliente pelo ID
    public function delete(int $id): bool
    {
        // Prepara o DELETE
        $stmt = $this->conn->prepare('DELETE FROM clientes WHERE id = :id');

        // Executa e retorna true ou false
        return $stmt->execute([':id' => $id]);
    }
}