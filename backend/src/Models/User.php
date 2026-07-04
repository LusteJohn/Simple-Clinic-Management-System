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

    public function update(int $userId, array $data): bool
    {
        $allowedFields = ['username', 'email', 'password', 'role'];

        $fields = [];
        $params = ['user_id' => $userId];

        foreach ($allowedFields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            if ($field === 'password') {
                if (trim($value) === '') {
                    continue;
                }

                $value = password_hash($value, PASSWORD_DEFAULT);
            }

            $fields[] = "$field = :$field";
            $params[$field] = $value;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE users
                SET " . implode(', ', $fields) . "
                WHERE user_id = :user_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    public function delete(int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE user_id = :user_id");
        return $stmt->execute(['user_id' => $userId]);
    }
}