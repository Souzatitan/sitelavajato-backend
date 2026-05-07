<?php

/*

Classe base para os controllers.

Ela contém métodos comuns que podem ser usados
por outros controllers da aplicação.

*/

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    // Lê o corpo da requisição JSON
    protected function input(): array
    {
        // Pega o conteúdo bruto enviado na requisição
        $raw = file_get_contents('php://input');

        // Se não veio nada, retorna array vazio
        if ($raw === false || $raw === '') {
            return [];
        }

        // Converte o JSON recebido em array PHP
        $data = json_decode($raw, true);

        // Se for um array válido, retorna os dados
        // Caso contrário, retorna array vazio
        return is_array($data) ? $data : [];
    }

    // Verifica se os campos obrigatórios foram enviados
    protected function requireFields(array $data, array $fields): void
    {
        // Percorre a lista de campos obrigatórios
        foreach ($fields as $field) {
            // Verifica se o campo não existe, está vazio ou está nulo
            if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
                Response::json(['erro' => "Campo obrigatório: {$field}"], 422);
            }
        }
    }
}