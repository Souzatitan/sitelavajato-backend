<?php

/*

Model responsável por acessar a tabela de horários.
Ele lista, busca, cria, atualiza, exclui e consulta horários disponíveis.

*/

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Horario
{
    // Guarda a conexão com o banco
    private PDO $conn;

    // Ao criar o model, pega a conexão com o banco
    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // Lista todos os horários cadastrados
    public function findAll(): array
    {
        // Executa a consulta ordenando por data e hora
        $stmt = $this->conn->query('SELECT id, data, hora FROM horarios ORDER BY data ASC, hora ASC');

        // Retorna todos os horários encontrados
        return $stmt->fetchAll();
    }

    // Lista horários disponíveis, podendo filtrar por data
    public function findAvailable(?string $date = null): array
    {
        // Busca horários que não possuem agendamento ativo
        $sql = 'SELECT h.id, h.data, h.hora
                FROM horarios h
                LEFT JOIN agendamentos a ON a.horario_id = h.id AND a.status IN (\'agendado\', \'concluido\')
                WHERE a.id IS NULL';

        // Parâmetros da consulta
        $params = [];

        // Se uma data foi informada, adiciona filtro por data
        if ($date) {
            $sql .= ' AND h.data = :data';
            $params[':data'] = $date;
        }

        // Ordena por data e hora
        $sql .= ' ORDER BY h.data ASC, h.hora ASC';

        // Prepara e executa a consulta
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        // Retorna os horários disponíveis
        return $stmt->fetchAll();
    }

    // Busca um horário pelo ID
    public function findById(int $id): ?array
    {
        // Prepara a consulta
        $stmt = $this->conn->prepare('SELECT id, data, hora FROM horarios WHERE id = :id');

        // Executa passando o ID
        $stmt->execute([':id' => $id]);

        // Pega o resultado
        $row = $stmt->fetch();

        // Retorna o horário ou null
        return $row ?: null;
    }

    // Cria um novo horário
    public function create(array $data): int
    {
        // Prepara o INSERT
        $stmt = $this->conn->prepare(
            'INSERT INTO horarios (data, hora)
             VALUES (:data, :hora)
             RETURNING id'
        );

        // Executa passando data e hora
        $stmt->execute([
            ':data' => $data['data'],
            ':hora' => $data['hora'],
        ]);

        // Retorna o ID criado
        return (int) $stmt->fetchColumn();
    }

    // Atualiza um horário
    public function update(int $id, array $data): bool
    {
        // Prepara o UPDATE
        $stmt = $this->conn->prepare(
            'UPDATE horarios SET data = :data, hora = :hora WHERE id = :id'
        );

        // Executa passando ID, data e hora
        return $stmt->execute([
            ':id' => $id,
            ':data' => $data['data'],
            ':hora' => $data['hora'],
        ]);
    }

// Exclui um horário pelo ID
public function delete(int $id): bool
{
    // remove agendamentos ligados ao horário
    $stmtAgendamento = $this->conn->prepare(
        'DELETE FROM agendamentos WHERE horario_id = :id'
    );

    $stmtAgendamento->execute([
        ':id' => $id
    ]);

    // remove o horário
    $stmt = $this->conn->prepare(
        'DELETE FROM horarios WHERE id = :id'
    );

    return $stmt->execute([
        ':id' => $id
    ]);
}
}