<?php

namespace src\Models;

use src\Config\Database;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getAllUsers()
    {
        $stmt = $this->db->query("SELECT * FROM users");
        return $stmt->fetchAll();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT user_id, username, email, password, role FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);

        return $stmt->fetch();
    }

    public function findByUsernameOrEmail(string $identifier): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT user_id, username, email, password, role, is_active
             FROM users
             WHERE username = :identifier OR email = :identifier
             LIMIT 1"
        );
        $stmt->execute(['identifier' => $identifier]);

        return $stmt->fetch();
    }

    public function findById(int $userId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT user_id, username, email, password, role, is_active
             FROM users
             WHERE user_id = :user_id
             LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password, role, is_active, created_at)
             VALUES (:username, :email, :password, :role, :is_active, NOW())"
        );

        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'is_active' => $data['is_active'] ?? 1,
            'role' => $data['role'] ?? '',
        ]);

        return (int) $this->db->lastInsertId();
    }
}