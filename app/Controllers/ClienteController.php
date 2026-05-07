<?php

/*

Controller responsável por listar, buscar, atualizar
e excluir clientes.

*/

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Cliente;

class ClienteController extends Controller
{
    // Lista todos os clientes
    public function index(): void
    {
        // Apenas admin pode listar todos os clientes
        AuthMiddleware::requireAuth('admin');

        // Busca os clientes no banco
        $model = new Cliente();

        // Retorna a lista em JSON
        Response::json(['clientes' => $model->findAll()]);
    }

    // Busca um cliente pelo ID
    public function show(int $id): void
    {
        // Verifica se o usuário está logado
        $user = AuthMiddleware::requireAuth();

        // Cliente só pode consultar o próprio cadastro
        if (($user['tipo'] ?? null) === 'cliente' && (int) $user['id'] !== $id) {
            Response::json(['erro' => 'Acesso negado'], 403);
        }

        // Busca o cliente no banco
        $model = new Cliente();
        $cliente = $model->findById($id);

        // Retorna erro se não encontrar
        if (!$cliente) {
            Response::json(['erro' => 'Cliente não encontrado'], 404);
        }

        // Retorna os dados do cliente
        Response::json(['cliente' => $cliente]);
    }

    // Atualiza um cliente pelo ID
    public function update(int $id): void
    {
        // Verifica se o usuário está logado
        $user = AuthMiddleware::requireAuth();

        // Cliente só pode atualizar o próprio cadastro
        if (($user['tipo'] ?? null) === 'cliente' && (int) $user['id'] !== $id) {
            Response::json(['erro' => 'Acesso negado'], 403);
        }

        // Lê os dados enviados no JSON
        $data = $this->input();

        // Verifica se o cliente existe
        $model = new Cliente();
        if (!$model->findAuthById($id)) {
            Response::json(['erro' => 'Cliente não encontrado'], 404);
        }

        // Se o e-mail foi enviado, verifica se já está em uso por outro cliente
        if (isset($data['email'])) {
            $existing = $model->findByEmail($data['email']);
            if ($existing && (int) $existing['id'] !== $id) {
                Response::json(['erro' => 'E-mail já está em uso'], 409);
            }
        }

        // Atualiza os dados do cliente
        if (!$model->update($id, $data)) {
            Response::json(['erro' => 'Nenhum dado enviado para atualização'], 422);
        }

        // Retorna sucesso
        Response::json(['mensagem' => 'Cliente atualizado com sucesso']);
    }

    // Exclui um cliente pelo ID
    public function delete(int $id): void
    {
        // Verifica se o usuário está logado
        $user = AuthMiddleware::requireAuth();

        // Cliente só pode excluir o próprio cadastro
        if (($user['tipo'] ?? null) === 'cliente' && (int) $user['id'] !== $id) {
            Response::json(['erro' => 'Acesso negado'], 403);
        }

        // Verifica se o cliente existe
        $model = new Cliente();
        if (!$model->findAuthById($id)) {
            Response::json(['erro' => 'Cliente não encontrado'], 404);
        }

        // Exclui o cliente do banco
        $model->delete($id);

        // Retorna sucesso
        Response::json(['mensagem' => 'Cliente removido com sucesso']);
    }
}