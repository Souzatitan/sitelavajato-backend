<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Middleware\AuthMiddleware;

use App\Models\Agendamento;
use App\Models\Horario;

class AgendamentoController extends Controller
{
    // 📋 LISTAR AGENDAMENTOS
    public function index(): void
    {
        $user = AuthMiddleware::requireAuth();

        $model = new Agendamento();

        // admin vê todos
        if (($user['tipo'] ?? null) === 'admin') {

            Response::json([
                'agendamentos' => $model->findAll()
            ]);
        }

        // cliente vê os próprios
        Response::json([
            'agendamentos' => $model->findByClienteId(
                (int)$user['id']
            )
        ]);
    }

    // ➕ CRIAR AGENDAMENTO
    public function store(): void
{
    $user = AuthMiddleware::requireAuth();

    $data = $this->input();

    $this->requireFields($data, [
        'data',
        'hora'
    ]);

    $horarioModel = new Horario();

    $horarios = $horarioModel->findAll();

    $horarioEncontrado = null;

    foreach ($horarios as $horario) {

        if (
            $horario['data'] === $data['data']
            &&
            substr($horario['hora'], 0, 5)
            ===
            substr($data['hora'], 0, 5)
        ) {

            $horarioEncontrado = $horario;
            break;
        }
    }

    if (!$horarioEncontrado) {

        Response::json([
            'erro' => 'Horário não encontrado'
        ], 404);
    }

    $model = new Agendamento();

    if (
        $model->isHorarioBooked(
            (int)$horarioEncontrado['id']
        )
    ) {

        Response::json([
            'erro' => 'Horário já ocupado'
        ], 409);
    }

    $id = $model->create([

        'cliente_id' => (int)$user['id'],

        // serviço padrão
        'servico_id' => 1,

        'horario_id' => (int)$horarioEncontrado['id'],

        'observacoes' => null,

        'status' => 'agendado',
    ]);

    Response::json([
        'mensagem' => 'Agendamento criado com sucesso',
        'id' => $id
    ], 201);
}

    // ✅ CONFIRMAR AGENDAMENTO
    public function confirm(int $id): void
    {
        // apenas admin
        AuthMiddleware::requireAuth('admin');

        $model = new Agendamento();

        // verifica existência
        if (!$model->findById($id)) {

            Response::json([
                'erro' => 'Agendamento não encontrado'
            ], 404);
        }

        // atualiza status
        $model->updateStatus(
            $id,
            'confirmado'
        );

        Response::json([
            'mensagem' => 'Agendamento confirmado'
        ]);
    }

    // ❌ CANCELAR
    public function cancel(int $id): void
    {
        $user = AuthMiddleware::requireAuth();

        $model = new Agendamento();

        $agendamento = $model->findById($id);

        if (!$agendamento) {

            Response::json([
                'erro' => 'Agendamento não encontrado'
            ], 404);
        }

        // cliente só cancela o próprio
        if (
            ($user['tipo'] ?? null) === 'cliente'
            &&
            (int)$agendamento['cliente_id']
            !==
            (int)$user['id']
        ) {

            Response::json([
                'erro' => 'Acesso negado'
            ], 403);
        }

        // cancela
        $model->updateStatus(
            $id,
            'cancelado'
        );

        Response::json([
            'mensagem' => 'Agendamento cancelado'
        ]);
    }

    // 🗑 EXCLUIR
    public function delete(int $id): void
    {
        AuthMiddleware::requireAuth('admin');

        $model = new Agendamento();

        if (!$model->findById($id)) {

            Response::json([
                'erro' => 'Agendamento não encontrado'
            ], 404);
        }

        $model->delete($id);

        Response::json([
            'mensagem' => 'Agendamento removido'
        ]);
    }
}