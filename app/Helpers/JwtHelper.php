<?php

/*

Classe auxiliar responsável por gerar e validar tokens JWT.
JWT é usado para autenticar cliente ou administrador após o login.

*/

declare(strict_types=1);

namespace App\Helpers;

// Classe da biblioteca firebase/php-jwt usada para gerar tokens
use Firebase\JWT\JWT;

// Classe usada para validar/decodificar tokens
use Firebase\JWT\Key;

class JwtHelper
{
    // Gera um token JWT com os dados do usuário
    public static function generate(array $payload): string
    {
        // Pega o horário atual em segundos
        $now = time();

        // Pega a chave secreta do .env
        $secret = $_ENV['JWT_SECRET'];

        // Cria e retorna o token JWT
        return JWT::encode([
            // Identifica quem emitiu o token
            'iss' => 'agenda-api',

            // Data/hora em que o token foi criado
            'iat' => $now,

            // Data/hora de expiração do token
            // 86400 segundos = 24 horas
            'exp' => $now + 86400,

            // Dados do usuário logado
            'data' => $payload,
        ], $secret, 'HS256');
    }

    // Decodifica e valida um token JWT recebido
    public static function decode(string $token): object
    {
        // Pega a mesma chave secreta usada para gerar o token
        $secret = $_ENV['JWT_SECRET'];

        // Valida e decodifica o token usando o algoritmo HS256
        return JWT::decode($token, new Key($secret, 'HS256'));
    }
}