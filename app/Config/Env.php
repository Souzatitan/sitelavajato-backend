<?php

/*

Este arquivo contém a classe Env.

Ela é responsável por ler o arquivo .env
e carregar suas variáveis para dentro da aplicação.

*/

declare(strict_types=1);

namespace App\Config;

class Env
{
    // Carrega as variáveis do arquivo .env
    public static function load(string $path): void
    {
        // Verifica se o arquivo .env existe no caminho informado
        if (!file_exists($path)) {
            throw new \RuntimeException('.env não encontrado em ' . $path);
        }

        // Lê o arquivo linha por linha, ignorando linhas vazias
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Percorre cada linha do arquivo .env
        foreach ($lines as $line) {
            // Remove espaços no começo e no final da linha
            $line = trim($line);

            // Ignora linhas vazias e comentários iniciados com #
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Separa a linha em chave e valor usando o sinal =
            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');

            // Remove espaços extras da chave
            $key = trim($key);

            // Remove espaços extras do valor
            $value = trim($value);

            // Salva a variável dentro de $_ENV
            $_ENV[$key] = $value;

            // Também salva a variável no ambiente do PHP
            putenv("{$key}={$value}");
        }
    }
}