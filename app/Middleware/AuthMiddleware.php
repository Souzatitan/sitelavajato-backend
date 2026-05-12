<?php

/*

Middleware responsável por proteger rotas.

Ele verifica se a requisição possui um token JWT válido
e, opcionalmente, se o usuário tem o tipo/perfil exigido.

*/

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Helpers\JwtHelper;

class AuthMiddleware
{
    // Verifica se o usuário está autenticado
    public static function requireAuth(?string $role = null): array
    {
        // Pega os headers da requisição
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        // Procura o header Authorization
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        // Verifica se o token foi enviado no formato Bearer
        if (!$authHeader || !preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            Response::json(['erro' => 'Token não enviado'], 401);
        }

        try {
            // Decodifica e valida o token JWT
            $decoded = JwtHelper::decode($matches[1]);

            // Pega os dados do usuário dentro do token
            $userData = (array) $decoded->data;

            // Se uma role foi exigida, valida se o usuário possui essa role
            if ($role !== null && (($userData['tipo'] ?? null) !== $role)) {
                Response::json(['erro' => 'Acesso negado'], 403);
            }

            // Retorna os dados do usuário autenticado
            return $userData;
        } catch (\Throwable $e) {
            // Se o token for inválido ou expirado, retorna erro
            Response::json(['erro' => 'Token inválido ou expirado'], 401);
        }
    }
}