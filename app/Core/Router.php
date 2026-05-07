<?php

/*

Classe responsável por registrar rotas e direcionar
cada requisição para o controller correto.

*/

declare(strict_types=1);

namespace App\Core;

class Router
{
    // Lista onde as rotas cadastradas ficam armazenadas
    private array $routes = [];

    // Adiciona uma nova rota
    public function add(string $method, string $path, callable|array $handler): void
    {
        // Guarda método, caminho e função/controller que será executado
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
        ];
    }

    // Executa a rota correspondente à URL chamada
    public function dispatch(string $uri, string $method): void
    {
        // Pega apenas o caminho da URL, removendo query string
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';

        // Padroniza o método em maiúsculo
        $method = strtoupper($method);

        // Percorre todas as rotas cadastradas
        foreach ($this->routes as $route) {
            // Transforma parâmetros como {id} em regex numérico
            $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([0-9]+)', $route['path']);

            // Monta o padrão completo para comparar com a URL
            $pattern = '#^' . $pattern . '$#';

            // Verifica se método e URL combinam com a rota
            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                // Remove o primeiro item, que é a URL completa encontrada
                array_shift($matches);

                // Pega o controller/função da rota
                $handler = $route['handler'];

                // Se o handler for um array, chama controller e método
                if (is_array($handler)) {
                    [$class, $action] = $handler;

                    // Cria o controller
                    $controller = new $class();

                    // Executa o método, passando parâmetros da URL
                    $controller->$action(...$matches);
                    return;
                }

                // Se for uma função comum, executa a função
                call_user_func_array($handler, $matches);
                return;
            }
        }

        // Se nenhuma rota combinar, retorna erro 404
        Response::json(['erro' => 'Rota não encontrada'], 404);
    }
}