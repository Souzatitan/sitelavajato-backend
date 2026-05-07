<?php

/*

Controller responsável por gerenciar os serviços.
Clientes podem listar e visualizar.
Somente admin pode criar, atualizar e excluir.

*/

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Servico;

class ServicoController extends Controller
{
    // Lista todos os serviços
    public function index(): void
    {
        // Cria o model de serviço
        $model = new Servico();

        // Retorna todos os serviços em JSON
        Response::json(['servicos' => $model->findAll()]);
    }

    // Busca um serviço pelo ID
    public function show(int $id): void
    {
        // Cria o model de serviço
        $model = new Servico();

        // Busca o serviço no banco
        $servico = $model->findById($id);

        // Retorna erro se não encontrar
        if (!$servico) {
            Response::json(['erro' => 'Serviço não encontrado'], 404);
        }

        // Retorna o serviço encontrado
        Response::json(['servico' => $servico]);
    }

    // Cria um novo serviço
    public function store(): void
    {
        // Apenas admin pode criar serviços
        AuthMiddleware::requireAuth('admin');

        // Lê os dados enviados no JSON
        $data = $this->input();

        // Exige nome e preço
        $this->requireFields($data, ['nome', 'preco']);

        // Cria o model de serviço
        $model = new Servico();

        // Salva o serviço no banco
        $id = $model->create($data);

        // Retorna sucesso
        Response::json(['mensagem' => 'Serviço criado com sucesso', 'id' => $id], 201);
    }

    // Atualiza um serviço
    public function update(int $id): void
    {
        // Apenas admin pode atualizar serviços
        AuthMiddleware::requireAuth('admin');

        // Lê os dados enviados no JSON
        $data = $this->input();

        // Exige nome e preço
        $this->requireFields($data, ['nome', 'preco']);

        // Cria o model de serviço
        $model = new Servico();

        // Verifica se o serviço existe
        if (!$model->findById($id)) {
            Response::json(['erro' => 'Serviço não encontrado'], 404);
        }

        // Atualiza o serviço no banco
        $model->update($id, $data);

        // Retorna sucesso
        Response::json(['mensagem' => 'Serviço atualizado com sucesso']);
    }

    // Exclui um serviço
    public function delete(int $id): void
    {
        // Apenas admin pode excluir serviços
        AuthMiddleware::requireAuth('admin');

        // Cria o model de serviço
        $model = new Servico();

        // Verifica se o serviço existe
        if (!$model->findById($id)) {
            Response::json(['erro' => 'Serviço não encontrado'], 404);
        }

        // Exclui o serviço do banco
        $model->delete($id);

        // Retorna sucesso
        Response::json(['mensagem' => 'Serviço removido com sucesso']);
    }
}