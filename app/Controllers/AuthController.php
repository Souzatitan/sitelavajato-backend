<?php

/*

Controller responsável por cadastro, login e identificação
do usuário autenticado.

*/

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Helpers\JwtHelper;
use App\Middleware\AuthMiddleware;
use App\Models\Administrador;
use App\Models\Cliente;

class AuthController extends Controller
{
    // Cadastra um novo cliente
    public function registerCliente(): void
    {
        // Lê os dados enviados no JSON
        $data = $this->input();

        // Exige nome, email e senha
        $this->requireFields($data, ['nome', 'email', 'senha']);

        // Verifica se o e-mail já existe
        $clienteModel = new Cliente();
        if ($clienteModel->findByEmail($data['email'])) {
            Response::json(['erro' => 'E-mail já cadastrado'], 409);
        }

        // Cria o cliente no banco
        $id = $clienteModel->create($data);

        // Retorna sucesso
        Response::json([
            'mensagem' => 'Cliente cadastrado com sucesso',
            'id' => $id,
        ], 201);
    }

    // Faz login do cliente
    public function loginCliente(): void
    {
        // Lê os dados enviados no JSON
        $data = $this->input();

        // Exige email e senha
        $this->requireFields($data, ['email', 'senha']);

        // Busca o cliente pelo e-mail
        $clienteModel = new Cliente();
        $cliente = $clienteModel->findByEmail($data['email']);

        // Valida se o cliente existe e se a senha está correta
        if (!$cliente || !password_verify($data['senha'], $cliente['senha'])) {
            Response::json(['erro' => 'Credenciais inválidas'], 401);
        }

        // Gera o token JWT do cliente
        $token = JwtHelper::generate([
            'id' => (int) $cliente['id'],
            'tipo' => 'cliente',
            'nome' => $cliente['nome'],
        ]);

        // Retorna o token e os dados do cliente
        Response::json([
            'token' => $token,
            'usuario' => [
                'id' => (int) $cliente['id'],
                'nome' => $cliente['nome'],
                'email' => $cliente['email'],
                'tipo' => 'cliente',
            ],
        ]);
    }

    // Faz login do administrador
    public function loginAdmin(): void
    {
        // Lê os dados enviados no JSON
        $data = $this->input();

        // Exige email e senha
        $this->requireFields($data, ['email', 'senha']);

        // Busca o administrador pelo e-mail
        $adminModel = new Administrador();
        $admin = $adminModel->findByEmail($data['email']);

        // Valida se o admin existe e se a senha está correta
        if (!$admin || !password_verify($data['senha'], $admin['senha'])) {
            Response::json(['erro' => 'Credenciais inválidas'], 401);
        }

        // Gera o token JWT do admin
        $token = JwtHelper::generate([
            'id' => (int) $admin['id'],
            'tipo' => 'admin',
            'nome' => $admin['nome'],
        ]);

        // Retorna o token e os dados do admin
        Response::json([
            'token' => $token,
            'usuario' => [
                'id' => (int) $admin['id'],
                'nome' => $admin['nome'],
                'email' => $admin['email'],
                'tipo' => 'admin',
            ],
        ]);
    }

    // Retorna os dados do usuário logado
    public function me(): void
    {
        // Verifica o token e pega os dados do usuário autenticado
        $user = AuthMiddleware::requireAuth();

        // Se for admin, busca na tabela de administradores
        if (($user['tipo'] ?? null) === 'admin') {
            $adminModel = new Administrador();
            $admin = $adminModel->findById((int) $user['id']);

            // Retorna erro se o admin não existir
            if (!$admin) {
                Response::json(['erro' => 'Administrador não encontrado'], 404);
            }

            // Retorna dados do admin logado
            Response::json(['usuario' => $admin + ['tipo' => 'admin']]);
        }

        // Se não for admin, busca na tabela de clientes
        $clienteModel = new Cliente();
        $cliente = $clienteModel->findById((int) $user['id']);

        // Retorna erro se o cliente não existir
        if (!$cliente) {
            Response::json(['erro' => 'Cliente não encontrado'], 404);
        }

        // Retorna dados do cliente logado
        Response::json(['usuario' => $cliente + ['tipo' => 'cliente']]);
    }
}