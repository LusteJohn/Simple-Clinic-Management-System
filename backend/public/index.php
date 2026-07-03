<?php

require __DIR__ . '/../vendor/autoload.php';

use src\Helpers\Router;
use src\Controllers\HomeController;
use src\Controllers\AuthController;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

$router = new Router();

$allowedOrigin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost:5173';
header("Access-Control-Allow-Origin: {$allowedOrigin}");
header('Vary: Origin');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(204);
	exit;
}

// Admin route for creating doctor accounts
$router->post('/api/admin/doctors/register', [AuthController::class, 'registerDoctor']);

// Define routes
$router->get('/', [HomeController::class, 'index']);
$router->post('/api/register', [AuthController::class, 'register']);
$router->post('/api/login', [AuthController::class, 'login']);
$router->get('/api/me', [AuthController::class, 'me']);
$router->post('/api/logout', [AuthController::class, 'logout']);

$requestPath = $_GET['route'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

if ($basePath !== '' && $basePath !== '/' && str_starts_with($requestPath, $basePath)) {
	$requestPath = substr($requestPath, strlen($basePath));
}

$requestPath = $requestPath ?: '/';

$router->dispatch($requestPath, $_SERVER['REQUEST_METHOD']);