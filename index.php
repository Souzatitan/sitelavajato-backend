<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Router;
use App\Core\Response;

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\HorarioController;
use App\Controllers\AgendamentoController;

$router = new Router();

/*
|--------------------------------------------------------------------------
| ROTAS ADMIN
|--------------------------------------------------------------------------
*/

$router->add('POST', '/admin/login', [AdminController::class, 'login']);

/*
|--------------------------------------------------------------------------
| ROTAS CLIENTES
|--------------------------------------------------------------------------
*/

$router->add('POST', '/clientes/register', [AuthController::class, 'registerCliente']);
$router->add('POST', '/clientes/login', [AuthController::class, 'loginCliente']);

$router->add('GET', '/me', [AuthController::class, 'me']);

/*
|--------------------------------------------------------------------------
| HORÁRIOS
|--------------------------------------------------------------------------
*/

$router->add('GET', '/horarios', [HorarioController::class, 'index']);
$router->add('POST', '/horarios', [HorarioController::class, 'store']);
$router->add('DELETE', '/horarios/{id}', [HorarioController::class, 'delete']);

/*
|--------------------------------------------------------------------------
| AGENDAMENTOS
|--------------------------------------------------------------------------
*/

$router->add('GET', '/agendamentos', [AgendamentoController::class, 'index']);

$router->add('POST', '/agendamentos', [AgendamentoController::class, 'store']);

$router->add('PATCH', '/agendamentos/{id}/status', [AgendamentoController::class, 'confirm']);

$router->add('PATCH', '/agendamentos/{id}/cancelar', [AgendamentoController::class, 'cancel']);

$router->add('DELETE', '/agendamentos/{id}', [AgendamentoController::class, 'delete']);

/*
|--------------------------------------------------------------------------
| EXECUTA
|--------------------------------------------------------------------------
*/

try {
    $router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
} catch (\Throwable $e) {
    Response::json([
        'erro' => $e->getMessage(),
        'linha' => $e->getLine(),
        'arquivo' => $e->getFile()
    ], 500);
}