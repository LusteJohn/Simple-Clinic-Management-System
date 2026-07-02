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
        $stmt = $this->db->prepare("SELECT id, username, email, password, role FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);

        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password_hash, role, is_active, created_at, updated_at)
             VALUES (:username, :email, :password_hash, :role, 1, NOW(), NOW())"
        );

        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'is_active' => $data['is_active'] ?? 1,
            'role' => $data['role'] ?? 'patient',
        ]);

        return (int) $this->db->lastInsertId();
    }
}