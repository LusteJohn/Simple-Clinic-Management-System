<?php

namespace src\Models;

use src\Config\Database;

class Doctor {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getDoctorById(int $userId): array|false {
        $stmt = $this->db->prepare(
            "SELECT user_id, firstname, middlename, lastname, name_ext, gender
            FROM doctors
            WHERE user_id = :user_id
            LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getDoctorIdByUserId(int $userId): int|false
    {
        $stmt = $this->db->prepare("
            SELECT doctor_id
            FROM doctors
            WHERE user_id = :user_id
            LIMIT 1
        ");

        $stmt->execute([
            'user_id' => $userId
        ]);

        $doctor = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $doctor ? (int)$doctor['doctor_id'] : false;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO doctors (user_id, firstname, middlename, lastname, name_ext, gender, created_at)
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
            "UPDATE doctors
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
        $stmt = $this->db->prepare("DELETE FROM doctors WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }
}