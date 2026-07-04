<?php

namespace src\Controllers;

use src\Helpers\Controller;
use src\Middleware\AdminMiddleware;
use src\Models\User;

class UserController extends Controller
{
    public function getAllUsers(): void
    {
        AdminMiddleware::handle();

        $userModel = new User();

        $this->json([
            'users' => $userModel->getAllUsers()
        ]);
    }

    public function getUserById(int $userId): void
    {
        AdminMiddleware::handle();

        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user || $user['role'] !== 'doctor') {
            $this->json([
                'message' => 'Doctor account not found.'
            ], 404);

            return;
        }

        $this->json([
            'user' => $user
        ]);
    }

    public function edit(int $userId): void
    {
        AdminMiddleware::handle();

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user || $user['role'] !== 'doctor') {
            $this->json([
                'message' => 'Doctor account not found.'
            ], 404);

            return;
        }

        $data = [];

        if (isset($payload['username'])) {
            $data['username'] = trim($payload['username']);
        }

        if (isset($payload['email'])) {
            if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
                $this->json([
                    'message' => 'Invalid email address.'
                ], 422);

                return;
            }

            $data['email'] = trim($payload['email']);
        }

        if (!empty($payload['password'])) {
            if (strlen($payload['password']) < 8) {
                $this->json([
                    'message' => 'Password must be at least 8 characters.'
                ], 422);

                return;
            }

            $data['password'] = $payload['password'];
        }

        if (empty($data)) {
            $this->json([
                'message' => 'No changes submitted.'
            ], 422);

            return;
        }

        $userModel->update($userId, $data);

        $this->json([
            'message' => 'Doctor account updated successfully.'
        ]);
    }

    public function delete(int $userId): void
    {
        AdminMiddleware::handle();

        $userModel = new User();

        $user = $userModel->findById($userId);

        if (!$user || $user['role'] !== 'doctor') {
            $this->json([
                'message' => 'Doctor account not found.'
            ], 404);

            return;
        }

        if (!$userModel->delete($userId)) {
            $this->json([
                'message' => 'Failed to delete doctor account.'
            ], 500);

            return;
        }

        $this->json([
            'message' => 'Doctor account deleted successfully.'
        ]);
    }

}