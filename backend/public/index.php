<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use src\Helpers\Router;
use src\Controllers\HomeController;
use src\Controllers\AuthController;
use src\Controllers\UserController;
use src\Controllers\DoctorController;
use src\Controllers\DoctorInfoController;
use src\Controllers\DoctorScheduleController;
use src\Controllers\PatientController;

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
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(204);
	exit;
}

// Admin route for creating doctor accounts
$router->post('/api/admin/doctors/register', [AuthController::class, 'registerDoctor']);
$router->get('/api/admin/users', [UserController::class, 'getAllUsers']);
$router->get('/api/admin/users/{userId}', [UserController::class, 'getUserById']);
$router->put('/api/admin/doctors/{userId}', [UserController::class, 'edit']);
$router->delete('/api/admin/doctors/{userId}', [UserController::class, 'delete']);

// Define routes
$router->get('/', [HomeController::class, 'index']);
$router->post('/api/register', [AuthController::class, 'register']);
$router->post('/api/login', [AuthController::class, 'login']);
$router->get('/api/me', [AuthController::class, 'me']);
$router->post('/api/logout', [AuthController::class, 'logout']);

// Doctor profile routes
$router->get('/api/doctor/profile', [DoctorController::class, 'getDoctorProfile']);
$router->post('/api/doctor/profile', [DoctorController::class, 'addDoctorProfile']);
$router->put('/api/doctor/profile', [DoctorController::class, 'editDoctorProfile']);
$router->delete('/api/doctor/profile', [DoctorController::class, 'deleteDoctorProfile']);

// Doctor info routes
$router->get('/api/doctor/info', [DoctorInfoController::class, 'getDoctorInfo']);
$router->post('/api/doctor/info', [DoctorInfoController::class, 'addDoctorInfo']);
$router->put('/api/doctor/info', [DoctorInfoController::class, 'editDoctorInfo']);
$router->delete('/api/doctor/info', [DoctorInfoController::class, 'deleteDoctorInfo']);

// Doctor schedule routes
$router->get('/api/doctor/schedule', [DoctorScheduleController::class, 'getDoctorSchedule']);
$router->post('/api/doctor/schedule', [DoctorScheduleController::class, 'addDoctorSchedule']);
$router->put('/api/doctor/schedule/{scheduleId}', [DoctorScheduleController::class, 'editDoctorSchedule']);
$router->delete('/api/doctor/schedule/{scheduleId}', [DoctorScheduleController::class, 'deleteDoctorSchedule']);

// Patient profile routes
$router->get('/api/patient/profile', [PatientController::class, 'getPatientProfile']);
$router->post('/api/patient/profile', [PatientController::class, 'addPatientProfile']);
$router->put('/api/patient/profile', [PatientController::class, 'editPatientProfile']);
$router->delete('/api/patient/profile', [PatientController::class, 'deletePatientProfile']);

$requestPath = $_GET['route'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

if ($basePath !== '' && $basePath !== '/' && str_starts_with($requestPath, $basePath)) {
	$requestPath = substr($requestPath, strlen($basePath));
}

$requestPath = $requestPath ?: '/';

$router->dispatch($requestPath, $_SERVER['REQUEST_METHOD']);