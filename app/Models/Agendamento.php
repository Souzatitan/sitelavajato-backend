<?php

/*

Model responsável por acessar a tabela de agendamentos
e fazer consultas, cadastros, atualizações e exclusões.

*/

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Agendamento
{
    // Guarda a conexão com o banco
    private PDO $conn;

    // Ao criar o model, pega a conexão com o banco
    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // Lista todos os agendamentos, com dados do cliente, serviço e horário
    public function findAll(): array
    {
        $sql = 'SELECT a.id, a.status, a.observacoes, a.criado_em, a.atualizado_em,
                       c.id AS cliente_id, c.nome AS cliente_nome,
                       s.id AS servico_id, s.nome AS servico_nome, s.preco,
                       h.id AS horario_id, h.data, h.hora
                FROM agendamentos a
                INNER JOIN clientes c ON c.id = a.cliente_id
                INNER JOIN servicos s ON s.id = a.servico_id
                INNER JOIN horarios h ON h.id = a.horario_id
                ORDER BY h.data ASC, h.hora ASC';

        // Executa a consulta diretamente, pois não tem parâmetros externos
        $stmt = $this->conn->query($sql);

        // Retorna todos os registros encontrados
        return $stmt->fetchAll();
    }

    // Lista agendamentos de um cliente específico
    public function findByClienteId(int $clienteId): array
    {
        $sql = 'SELECT a.id, a.status, a.observacoes, a.criado_em, a.atualizado_em,
                       s.id AS servico_id, s.nome AS servico_nome, s.preco,
                       h.id AS horario_id, h.data, h.hora
                FROM agendamentos a
                INNER JOIN servicos s ON s.id = a.servico_id
                INNER JOIN horarios h ON h.id = a.horario_id
                WHERE a.cliente_id = :cliente_id
                ORDER BY h.data ASC, h.hora ASC';

        // Prepara a consulta porque usa cliente_id externo
        $stmt = $this->conn->prepare($sql);

        // Executa passando o ID do cliente
        $stmt->execute([':cliente_id' => $clienteId]);

        // Retorna todos os agendamentos desse cliente
        return $stmt->fetchAll();
    }

    // Busca um agendamento pelo ID
    public function findById(int $id): ?array
    {
        $sql = 'SELECT a.*, c.nome AS cliente_nome, c.email AS cliente_email,
                       s.nome AS servico_nome, s.preco,
                       h.data, h.hora
                FROM agendamentos a
                INNER JOIN clientes c ON c.id = a.cliente_id
                INNER JOIN servicos s ON s.id = a.servico_id
                INNER JOIN horarios h ON h.id = a.horario_id
                WHERE a.id = :id';

        // Prepara a consulta
        $stmt = $this->conn->prepare($sql);

        // Executa passando o ID do agendamento
        $stmt->execute([':id' => $id]);

        // Pega apenas um registro
        $row = $stmt->fetch();

        // Retorna o agendamento ou null se não encontrar
        return $row ?: null;
    }

    // Cria um novo agendamento
    public function create(array $data): int
    {
        // Insere o agendamento e retorna o ID criado
        $stmt = $this->conn->prepare(
            'INSERT INTO agendamentos (cliente_id, servico_id, horario_id, status, observacoes)
             VALUES (:cliente_id, :servico_id, :horario_id, :status, :observacoes)
             RETURNING id'
        );

        // Executa o INSERT com os dados recebidos
        $stmt->execute([
            ':cliente_id' => $data['cliente_id'],
            ':servico_id' => $data['servico_id'],
            ':horario_id' => $data['horario_id'],
            ':status' => $data['status'] ?? 'agendado',
            ':observacoes' => $data['observacoes'] ?? null,
        ]);

        // Retorna o ID do agendamento criado
        return (int) $stmt->fetchColumn();
    }

    // Atualiza apenas o status do agendamento
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE agendamentos
             SET status = :status, atualizado_em = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        // Executa a atualização
        return $stmt->execute([':id' => $id, ':status' => $status]);
    }

    // Exclui um agendamento pelo ID
    public function delete(int $id): bool
    {
        // Prepara o DELETE
        $stmt = $this->conn->prepare('DELETE FROM agendamentos WHERE id = :id');

        // Executa e retorna true ou false
        return $stmt->execute([':id' => $id]);
    }

    // Verifica se um horário já está ocupado
    public function isHorarioBooked(int $horarioId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT 1 FROM agendamentos WHERE horario_id = :horario_id AND status IN ('agendado', 'concluido') LIMIT 1"
        );

        // Executa passando o ID do horário
        $stmt->execute([':horario_id' => $horarioId]);

        // Se encontrar algo, retorna true; senão, false
        return (bool) $stmt->fetchColumn();
    }
}