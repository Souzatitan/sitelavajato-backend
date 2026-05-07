<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Router;
use App\Controllers\AdminController;

$router = new Router();

/*
|--------------------------------------------------------------------------
| ROTAS
|--------------------------------------------------------------------------
*/

$router->add('POST', '/admin/login', [AdminController::class, 'login']);

/*
|--------------------------------------------------------------------------
| EXECUTA
|--------------------------------------------------------------------------
*/

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);