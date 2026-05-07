<?php

/*

Controller responsável por gerenciar pagamentos.
Somente administrador acessa.

*/

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Agendamento;
use App\Models\Pagamento;

class PagamentoController extends Controller
{
    // Formas de pagamento aceitas
    private array $formas = ['dinheiro', 'pix', 'cartao', 'outro'];

    // Status permitidos para pagamento
    private array $statusPermitidos = ['pendente', 'pago', 'cancelado'];

    // Lista todos os pagamentos
    public function index(): void
    {
        // Apenas admin pode listar pagamentos
        AuthMiddleware::requireAuth('admin');

        // Cria o model de pagamento
        $model = new Pagamento();

        // Retorna todos os pagamentos
        Response::json(['pagamentos' => $model->findAll()]);
    }

    // Busca um pagamento pelo ID
    public function show(int $id): void
    {
        // Apenas admin pode consultar pagamentos
        AuthMiddleware::requireAuth('admin');

        // Busca o pagamento no banco
        $model = new Pagamento();
        $pagamento = $model->findById($id);

        // Retorna erro se não encontrar
        if (!$pagamento) {
            Response::json(['erro' => 'Pagamento não encontrado'], 404);
        }

        // Retorna o pagamento encontrado
        Response::json(['pagamento' => $pagamento]);
    }

    // Cria um novo pagamento
    public function store(): void
    {
        // Apenas admin pode criar pagamentos
        AuthMiddleware::requireAuth('admin');

        // Lê os dados enviados no JSON
        $data = $this->input();

        // Exige agendamento e valor recebido
        $this->requireFields($data, ['agendamento_id', 'valor_recebido']);

        // Verifica se o agendamento existe
        $agendamentoModel = new Agendamento();
        if (!$agendamentoModel->findById((int) $data['agendamento_id'])) {
            Response::json(['erro' => 'Agendamento não encontrado'], 404);
        }

        // Valida forma de pagamento, se enviada
        if (isset($data['forma_pagamento']) && !in_array($data['forma_pagamento'], $this->formas, true)) {
            Response::json(['erro' => 'Forma de pagamento inválida'], 422);
        }

        // Valida status, se enviado
        if (isset($data['status']) && !in_array($data['status'], $this->statusPermitidos, true)) {
            Response::json(['erro' => 'Status de pagamento inválido'], 422);
        }

        // Verifica se já existe pagamento para esse agendamento
        $model = new Pagamento();
        if ($model->findByAgendamentoId((int) $data['agendamento_id'])) {
            Response::json(['erro' => 'Já existe pagamento para este agendamento'], 409);
        }

        // Salva o pagamento no banco
        $id = $model->create($data);

        // Retorna sucesso
        Response::json(['mensagem' => 'Pagamento criado com sucesso', 'id' => $id], 201);
    }

    // Atualiza um pagamento
    public function update(int $id): void
    {
        // Apenas admin pode atualizar pagamentos
        AuthMiddleware::requireAuth('admin');

        // Lê os dados enviados no JSON
        $data = $this->input();

        // Exige valor, forma de pagamento e status
        $this->requireFields($data, ['valor_recebido', 'forma_pagamento', 'status']);

        // Valida forma de pagamento
        if (!in_array($data['forma_pagamento'], $this->formas, true)) {
            Response::json(['erro' => 'Forma de pagamento inválida'], 422);
        }

        // Valida status do pagamento
        if (!in_array($data['status'], $this->statusPermitidos, true)) {
            Response::json(['erro' => 'Status de pagamento inválido'], 422);
        }

        // Verifica se o pagamento existe
        $model = new Pagamento();
        if (!$model->findById($id)) {
            Response::json(['erro' => 'Pagamento não encontrado'], 404);
        }

        // Atualiza o pagamento no banco
        $model->update($id, $data);

        // Retorna sucesso
        Response::json(['mensagem' => 'Pagamento atualizado com sucesso']);
    }

    // Exclui um pagamento
    public function delete(int $id): void
    {
        // Apenas admin pode excluir pagamentos
        AuthMiddleware::requireAuth('admin');

        // Verifica se o pagamento existe
        $model = new Pagamento();
        if (!$model->findById($id)) {
            Response::json(['erro' => 'Pagamento não encontrado'], 404);
        }

        // Exclui o pagamento do banco
        $model->delete($id);

        // Retorna sucesso
        Response::json(['mensagem' => 'Pagamento removido com sucesso']);
    }
}