<?php

/*

Controller responsável pelas ações de agendamento:
listar, buscar, criar, alterar status, cancelar e excluir.

*/

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Horario;
use App\Models\Servico;

class AgendamentoController extends Controller
{
    // Status permitidos para um agendamento
    private array $allowedStatus = ['agendado', 'concluido', 'cancelado'];

    // Lista agendamentos
    public function index(): void
    {
        // Verifica se o usuário está logado
        $user = AuthMiddleware::requireAuth();

        // Model usado para consultar agendamentos
        $model = new Agendamento();

        // Admin visualiza todos os agendamentos
        if (($user['tipo'] ?? null) === 'admin') {
            Response::json(['agendamentos' => $model->findAll()]);
        }

        // Cliente visualiza apenas os próprios agendamentos
        Response::json(['agendamentos' => $model->findByClienteId((int) $user['id'])]);
    }

    // Busca um agendamento pelo ID
    public function show(int $id): void
    {
        // Verifica se o usuário está logado
        $user = AuthMiddleware::requireAuth();

        // Busca o agendamento no banco
        $model = new Agendamento();
        $agendamento = $model->findById($id);

        // Retorna erro se não encontrar
        if (!$agendamento) {
            Response::json(['erro' => 'Agendamento não encontrado'], 404);
        }

        // Cliente só pode acessar o próprio agendamento
        if (($user['tipo'] ?? null) === 'cliente' && (int) $agendamento['cliente_id'] !== (int) $user['id']) {
            Response::json(['erro' => 'Acesso negado'], 403);
        }

        // Retorna o agendamento encontrado
        Response::json(['agendamento' => $agendamento]);
    }

    // Cria um novo agendamento
    public function store(): void
    {
        // Verifica se o usuário está logado
        $user = AuthMiddleware::requireAuth();

        // Lê os dados enviados no JSON
        $data = $this->input();

        // Exige serviço e horário
        $this->requireFields($data, ['servico_id', 'horario_id']);

        // Se for admin, usa cliente_id do JSON; se for cliente, usa o próprio ID
        $clienteId = ($user['tipo'] ?? null) === 'admin'
            ? (int) ($data['cliente_id'] ?? 0)
            : (int) $user['id'];

        // Admin precisa informar cliente_id
        if ($clienteId <= 0) {
            Response::json(['erro' => 'cliente_id é obrigatório para agendamento feito por administrador'], 422);
        }

        // Verifica se o cliente existe
        $clienteModel = new Cliente();
        if (!$clienteModel->findAuthById($clienteId)) {
            Response::json(['erro' => 'Cliente não encontrado'], 404);
        }

        // Verifica se o serviço existe
        $servicoModel = new Servico();
        if (!$servicoModel->findById((int) $data['servico_id'])) {
            Response::json(['erro' => 'Serviço não encontrado'], 404);
        }

        // Verifica se o horário existe
        $horarioModel = new Horario();
        if (!$horarioModel->findById((int) $data['horario_id'])) {
            Response::json(['erro' => 'Horário não encontrado'], 404);
        }

        // Verifica se o horário já está ocupado
        $model = new Agendamento();
        if ($model->isHorarioBooked((int) $data['horario_id'])) {
            Response::json(['erro' => 'Este horário já está ocupado'], 409);
        }

        // Salva o agendamento no banco
        $id = $model->create([
            'cliente_id' => $clienteId,
            'servico_id' => (int) $data['servico_id'],
            'horario_id' => (int) $data['horario_id'],
            'observacoes' => $data['observacoes'] ?? null,
            'status' => 'agendado',
        ]);

        // Retorna sucesso
        Response::json(['mensagem' => 'Agendamento criado com sucesso', 'id' => $id], 201);
    }

    // Atualiza o status do agendamento
    public function updateStatus(int $id): void
    {
        // Apenas admin pode alterar status
        AuthMiddleware::requireAuth('admin');

        // Lê o JSON enviado
        $data = $this->input();

        // Exige o campo status
        $this->requireFields($data, ['status']);

        // Valida se o status é permitido
        if (!in_array($data['status'], $this->allowedStatus, true)) {
            Response::json(['erro' => 'Status inválido'], 422);
        }

        // Verifica se o agendamento existe
        $model = new Agendamento();
        if (!$model->findById($id)) {
            Response::json(['erro' => 'Agendamento não encontrado'], 404);
        }

        // Atualiza o status no banco
        $model->updateStatus($id, $data['status']);

        // Retorna sucesso
        Response::json(['mensagem' => 'Status atualizado com sucesso']);
    }

    // Cancela um agendamento
    public function cancel(int $id): void
    {
        // Verifica se o usuário está logado
        $user = AuthMiddleware::requireAuth();

        // Busca o agendamento
        $model = new Agendamento();
        $agendamento = $model->findById($id);

        // Retorna erro se não encontrar
        if (!$agendamento) {
            Response::json(['erro' => 'Agendamento não encontrado'], 404);
        }

        // Cliente só pode cancelar o próprio agendamento
        if (($user['tipo'] ?? null) === 'cliente' && (int) $agendamento['cliente_id'] !== (int) $user['id']) {
            Response::json(['erro' => 'Acesso negado'], 403);
        }

        // Atualiza o status para cancelado
        $model->updateStatus($id, 'cancelado');

        // Retorna sucesso
        Response::json(['mensagem' => 'Agendamento cancelado com sucesso']);
    }

    // Exclui um agendamento
    public function delete(int $id): void
    {
        // Apenas admin pode excluir
        AuthMiddleware::requireAuth('admin');

        // Verifica se o agendamento existe
        $model = new Agendamento();
        if (!$model->findById($id)) {
            Response::json(['erro' => 'Agendamento não encontrado'], 404);
        }

        // Exclui do banco
        $model->delete($id);

        // Retorna sucesso
        Response::json(['mensagem' => 'Agendamento removido com sucesso']);
    }
}