<?php

declare(strict_types=1);

/*
Autoload do Composer
*/
require __DIR__ . '/../vendor/autoload.php';

/*
Importações
*/
use App\Config\Env;
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\ClienteController;
use App\Controllers\ServicoController;
use App\Controllers\HorarioController;
use App\Controllers\AgendamentoController;
use App\Controllers\PagamentoController;

/*
Carrega variáveis de ambiente
*/
Env::load(__DIR__ . '/../.env');

/*
CORS
*/
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

/*
Preflight (OPTIONS)
*/
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/*
🚀 CRIA O ROUTER (ANTES DE TUDO)
*/
$router = new Router();

/*
===========================
ROTAS DE AUTENTICAÇÃO
===========================
*/
$router->add('POST', '/api/clientes/register', [AuthController::class, 'registerCliente']);
$router->add('POST', '/api/clientes/login', [AuthController::class, 'loginCliente']);
$router->add('POST', '/api/admin/login', [AuthController::class, 'loginAdmin']);
$router->add('GET', '/api/me', [AuthController::class, 'me']);

/*
===========================
ROTAS DE CLIENTES
===========================
*/
$router->add('GET', '/api/clientes', [ClienteController::class, 'index']);
$router->add('GET', '/api/clientes/{id}', [ClienteController::class, 'show']);
$router->add('PUT', '/api/clientes/{id}', [ClienteController::class, 'update']);
$router->add('DELETE', '/api/clientes/{id}', [ClienteController::class, 'delete']);

/*
===========================
ROTAS DE SERVIÇOS
===========================
*/
$router->add('GET', '/api/servicos', [ServicoController::class, 'index']);
$router->add('GET', '/api/servicos/{id}', [ServicoController::class, 'show']);
$router->add('POST', '/api/servicos', [ServicoController::class, 'store']);
$router->add('PUT', '/api/servicos/{id}', [ServicoController::class, 'update']);
$router->add('DELETE', '/api/servicos/{id}', [ServicoController::class, 'delete']);

/*
===========================
ROTAS DE HORÁRIOS
===========================
*/
$router->add('GET', '/api/horarios', [HorarioController::class, 'index']);
$router->add('GET', '/api/horarios/disponiveis', [HorarioController::class, 'available']);
$router->add('GET', '/api/horarios/{id}', [HorarioController::class, 'show']);
$router->add('POST', '/api/horarios', [HorarioController::class, 'store']);
$router->add('PUT', '/api/horarios/{id}', [HorarioController::class, 'update']);
$router->add('DELETE', '/api/horarios/{id}', [HorarioController::class, 'delete']);

/*
===========================
ROTAS DE AGENDAMENTOS
===========================
*/
$router->add('GET', '/api/agendamentos', [AgendamentoController::class, 'index']);
$router->add('GET', '/api/agendamentos/{id}', [AgendamentoController::class, 'show']);
$router->add('POST', '/api/agendamentos', [AgendamentoController::class, 'store']);
$router->add('PATCH', '/api/agendamentos/{id}/status', [AgendamentoController::class, 'updateStatus']);
$router->add('PATCH', '/api/agendamentos/{id}/cancelar', [AgendamentoController::class, 'cancel']);
$router->add('DELETE', '/api/agendamentos/{id}', [AgendamentoController::class, 'delete']);

/*
===========================
ROTAS DE PAGAMENTOS
===========================
*/
$router->add('GET', '/api/pagamentos', [PagamentoController::class, 'index']);
$router->add('GET', '/api/pagamentos/{id}', [PagamentoController::class, 'show']);
$router->add('POST', '/api/pagamentos', [PagamentoController::class, 'store']);
$router->add('PUT', '/api/pagamentos/{id}', [PagamentoController::class, 'update']);
$router->add('DELETE', '/api/pagamentos/{id}', [PagamentoController::class, 'delete']);

/*
🚀 EXECUTA AS ROTAS
*/
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);