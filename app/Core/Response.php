<?php

/*

Classe responsável por padronizar as respostas da API.
Neste caso, ela retorna respostas em JSON.

*/

declare(strict_types=1);

namespace App\Core;

class Response
{
    // Retorna uma resposta JSON para o frontend
    public static function json(array $data, int $status = 200): void
    {
        // Define o código HTTP da resposta
        http_response_code($status);

        // Informa que a resposta será em formato JSON com UTF-8
        header('Content-Type: application/json; charset=utf-8');

        // Converte o array PHP para JSON e exibe na tela/resposta
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Encerra a execução após enviar a resposta
        exit;
    }
}