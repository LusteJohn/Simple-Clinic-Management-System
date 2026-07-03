<?php

namespace src\Controllers;

use src\Helpers\Controller;
use src\Models\User;

class AuthController extends Controller
{
    public function register(): void
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
            'role' => 'patient',
        ]);

        $this->json([
            'message' => 'Registration successful.',
            'user' => [
                'user_id' => $userId,
                'username' => $username,
                'email' => $email,
                'role' => 'patient',
            ],
        ], 201);
    }
}