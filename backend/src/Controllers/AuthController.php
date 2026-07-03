<?php

namespace src\Controllers;

use src\Helpers\Controller;
use src\Middleware\AdminMiddleware;
use src\Models\User;

class AuthController extends Controller
{
    private function createUser(string $role): void
    {
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        $username = trim((string) ($payload['username'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($username === '' || $email === '' || $password === '') {
            $this->json([
                'message' => 'Username, email, and password are required.',
            ], 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json([
                'message' => 'Please provide a valid email address.',
            ], 422);
            return;
        }

        if (strlen($password) < 8) {
            $this->json([
                'message' => 'Password must be at least 8 characters long.',
            ], 422);
            return;
        }

        $userModel = new User();

        if ($userModel->findByEmail($email)) {
            $this->json([
                'message' => 'Email is already registered.',
            ], 409);
            return;
        }

        $userId = $userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ]);

        $createdUser = $userModel->findById($userId);

        if (!$createdUser) {
            $this->json([
                'message' => ucfirst($role) . ' account could not be verified after saving.',
            ], 500);

            return;
        }

        $this->json([
            'message' => ucfirst($role) . ' account created successfully.',
            'user' => [
                'user_id' => (int) $createdUser['user_id'],
                'username' => $createdUser['username'],
                'email' => $createdUser['email'],
                'role' => $createdUser['role'],
            ],
        ], 201);
    }

    public function register(): void
    {
        // Public registration
        $this->createUser('patient');
    }

    public function registerDoctor(): void
    {
        AdminMiddleware::handle();
        $this->createUser('doctor');
    }

    public function login(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        $identifier = trim((string) ($payload['identifier'] ?? $payload['username'] ?? $payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($identifier === '' || $password === '') {
            $this->json([
                'message' => 'Username or email and password are required.',
            ], 422);

            return;
        }

        $userModel = new User();
        $user = $userModel->findByUsernameOrEmail($identifier);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->json([
                'message' => 'Invalid username/email or password.',
            ], 401);

            return;
        }

        $_SESSION['user'] = [
            'user_id' => (int) $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        $this->json([
            'message' => 'Login successful.',
            'user' => $_SESSION['user'],
        ]);
    }

    public function me(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->json([
                'message' => 'Not authenticated.',
            ], 401);

            return;
        }

        $this->json([
            'user' => $_SESSION['user'],
        ]);
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();

        $this->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}