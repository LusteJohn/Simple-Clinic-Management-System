<?php

namespace src\Models;

use src\Config\Database;

class Patient {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAllPatientByAdmin(): array {
        $stmt = $this->db->prepare(
            "SELECT user_id, firstname, middlename, lastname, name_ext, gender, created_at
            FROM patients
            ORDER BY created_at DESC"
        );
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPatientById(int $userId): array|false {
        $stmt = $this->db->prepare(
            "SELECT user_id, firstname, middlename, lastname, name_ext, gender
            FROM patients
            WHERE user_id = :user_id
            LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getPatientIdByUserId(int $userId): int|false
    {
        $stmt = $this->db->prepare("
            SELECT patient_id
            FROM patients
            WHERE user_id = :user_id
            LIMIT 1
        ");

        $stmt->execute([
            'user_id' => $userId
        ]);

        $doctor = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $doctor ? (int)$doctor['patient_id'] : false;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO patients (user_id, firstname, middlename, lastname, name_ext, gender, created_at)
            VALUES (:user_id, :firstname, :middlename, :lastname, :name_ext, :gender,NOW())"
        );

        $stmt->execute([
            'user_id' => $data['user_id'],
            'firstname' => $data['firstname'],
            'middlename' => $data['middlename'],
            'lastname' => $data['lastname'],
            'name_ext' => $data['name_ext'],
            'gender' => $data['gender'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $userId, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE patients
            SET firstname = :firstname,
                middlename = :middlename,
                lastname = :lastname,
                name_ext = :name_ext,
                gender = :gender
            WHERE user_id = :user_id
        ");

        $stmt->execute([
            'user_id' => $userId,
            'firstname' => $data['firstname'],
            'middlename' => $data['middlename'],
            'lastname' => $data['lastname'],
            'name_ext' => $data['name_ext'],
            'gender' => $data['gender'],
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM patients WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }
}