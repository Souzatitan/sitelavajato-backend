<?php

/*

Controller responsável por listar, buscar, criar,
atualizar, excluir e consultar horários disponíveis.

*/

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Horario;

class HorarioController extends Controller
{
    // Lista todos os horários
    public function index(): void
    {
        // Apenas admin pode listar todos os horários
     

        // Busca os horários no banco
        $model = new Horario();

        // Retorna a lista em JSON
        Response::json(['horarios' => $model->findAll()]);
    }

    // Lista horários disponíveis
    public function available(): void
    {
        // Pega a data enviada pela URL, se existir
        $date = $_GET['data'] ?? null;

        // Busca horários disponíveis no banco
        $model = new Horario();

        // Retorna horários disponíveis em JSON
        Response::json(['horarios' => $model->findAvailable($date)]);
    }

    // Busca um horário pelo ID
    public function show(int $id): void
    {
        // Apenas admin pode buscar horário específico
        AuthMiddleware::requireAuth('admin');

        // Busca o horário no banco
        $model = new Horario();
        $horario = $model->findById($id);

        // Retorna erro se não encontrar
        if (!$horario) {
            Response::json(['erro' => 'Horário não encontrado'], 404);
        }

        // Retorna o horário encontrado
        Response::json(['horario' => $horario]);
    }

    // Cria um novo horário
    // Cria um novo horário
public function store(): void
{
    // Apenas admin pode criar horários
    AuthMiddleware::requireAuth('admin');

    // Lê os dados enviados no JSON
    $data = $this->input();

    // Exige data e hora
    $this->requireFields($data, ['data', 'hora']);

    // Salva o horário no banco
    $model = new Horario();

    $id = $model->create([
        'data' => $data['data'],
        'hora' => $data['hora'],
    ]);

    // Retorna sucesso
    Response::json([
        'mensagem' => 'Horário criado com sucesso',
        'id' => $id
    ], 201);
}

    // Atualiza um horário
    public function update(int $id): void
    {
        // Apenas admin pode atualizar horários
        AuthMiddleware::requireAuth('admin');

        // Lê os dados enviados no JSON
        $data = $this->input();

        // Exige data e hora
        $this->requireFields($data, ['data', 'hora']);

        // Verifica se o horário existe
        $model = new Horario();
        if (!$model->findById($id)) {
            Response::json(['erro' => 'Horário não encontrado'], 404);
        }

        // Atualiza o horário no banco
        $model->update($id, $data);

        // Retorna sucesso
        Response::json(['mensagem' => 'Horário atualizado com sucesso']);
    }

    // Exclui um horário
public function delete($id): void
{
    $id = (int) $id;

    // Apenas admin pode excluir horários
    AuthMiddleware::requireAuth('admin');

    // Verifica se o horário existe
    $model = new Horario();

    if (!$model->findById($id)) {
        Response::json([
            'erro' => 'Horário não encontrado'
        ], 404);
    }

    // Exclui o horário
    $model->delete($id);

    // Retorna sucesso
    Response::json([
        'mensagem' => 'Horário removido com sucesso'
    ]);

        // Apenas admin pode excluir horários
        AuthMiddleware::requireAuth('admin');

        // Verifica se o horário existe
        $model = new Horario();
        if (!$model->findById($id)) {
            Response::json(['erro' => 'Horário não encontrado'], 404);
        }

        // Exclui o horário do banco
        $model->delete($id);

        // Retorna sucesso
        Response::json(['mensagem' => 'Horário removido com sucesso']);
    }
}