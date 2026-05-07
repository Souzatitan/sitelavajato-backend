<?php

/*

Model responsável por acessar a tabela de pagamentos.
Ele lista, busca, cria, atualiza e exclui pagamentos.

*/

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Pagamento
{
    // Guarda a conexão com o banco
    private PDO $conn;

    // Ao criar o model, pega a conexão com o banco
    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // Lista todos os pagamentos
    public function findAll(): array
    {
        // Busca pagamentos junto com dados do agendamento
        $sql = 'SELECT p.*, a.cliente_id, a.servico_id, a.horario_id
                FROM pagamentos p
                INNER JOIN agendamentos a ON a.id = p.agendamento_id
                ORDER BY p.id DESC';

        // Executa a consulta
        $stmt = $this->conn->query($sql);

        // Retorna todos os pagamentos
        return $stmt->fetchAll();
    }

    // Busca pagamento pelo ID
    public function findById(int $id): ?array
    {
        // Prepara a consulta
        $stmt = $this->conn->prepare('SELECT * FROM pagamentos WHERE id = :id');

        // Executa passando o ID
        $stmt->execute([':id' => $id]);

        // Pega o resultado
        $row = $stmt->fetch();

        // Retorna o pagamento ou null
        return $row ?: null;
    }

    // Busca pagamento pelo ID do agendamento
    public function findByAgendamentoId(int $agendamentoId): ?array
    {
        // Prepara a consulta
        $stmt = $this->conn->prepare('SELECT * FROM pagamentos WHERE agendamento_id = :agendamento_id LIMIT 1');

        // Executa passando o ID do agendamento
        $stmt->execute([':agendamento_id' => $agendamentoId]);

        // Pega o resultado
        $row = $stmt->fetch();

        // Retorna o pagamento ou null
        return $row ?: null;
    }

    // Cria um novo pagamento
    public function create(array $data): int
    {
        // Prepara o INSERT do pagamento
        $stmt = $this->conn->prepare(
            'INSERT INTO pagamentos (agendamento_id, valor_recebido, data_pagamento, forma_pagamento, status)
             VALUES (:agendamento_id, :valor_recebido, :data_pagamento, :forma_pagamento, :status)
             RETURNING id'
        );

        // Executa o INSERT com os dados recebidos
        $stmt->execute([
            ':agendamento_id' => $data['agendamento_id'],
            ':valor_recebido' => $data['valor_recebido'],
            ':data_pagamento' => $data['data_pagamento'] ?? null,
            ':forma_pagamento' => $data['forma_pagamento'] ?? 'pix',
            ':status' => $data['status'] ?? 'pendente',
        ]);

        // Retorna o ID do pagamento criado
        return (int) $stmt->fetchColumn();
    }

    // Atualiza um pagamento existente
    public function update(int $id, array $data): bool
    {
        // Prepara o UPDATE do pagamento
        $stmt = $this->conn->prepare(
            'UPDATE pagamentos
             SET valor_recebido = :valor_recebido,
                 data_pagamento = :data_pagamento,
                 forma_pagamento = :forma_pagamento,
                 status = :status
             WHERE id = :id'
        );

        // Executa a atualização
        return $stmt->execute([
            ':id' => $id,
            ':valor_recebido' => $data['valor_recebido'],
            ':data_pagamento' => $data['data_pagamento'] ?? null,
            ':forma_pagamento' => $data['forma_pagamento'],
            ':status' => $data['status'],
        ]);
    }

    // Exclui pagamento pelo ID
    public function delete(int $id): bool
    {
        // Prepara o DELETE
        $stmt = $this->conn->prepare('DELETE FROM pagamentos WHERE id = :id');

        // Executa e retorna true ou false
        return $stmt->execute([':id' => $id]);
    }
}