<?php

namespace src\Models;

use src\Config\Database;

class DoctorInfo {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getDoctorInfoById(int $userId): array|false {
        $stmt = $this->db->prepare("
            SELECT
                d.user_id,
                di.doctor_id,
                di.specialization,
                di.license_number,
                di.years_of_experience,
                di.consultation_fees
            FROM doctor_info di
            INNER JOIN doctors d ON di.doctor_id = d.doctor_id
            WHERE d.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO doctor_info (doctor_id, specialization, license_number, years_of_experience, consultation_fees, created_at)
            VALUES (:doctor_id, :specialization, :license_number, :years_of_experience, :consultation_fees, NOW())"
        );

        $success = $stmt->execute([
            'doctor_id' => $data['doctor_id'],
            'specialization' => $data['specialization'],
            'license_number' => $data['license_number'],
            'years_of_experience' => $data['years_of_experience'],
            'consultation_fees' => $data['consultation_fees'],
        ]);

        if (!$success) {
            die(print_r($stmt->errorInfo(), true));
        }

        return (int)$this->db->lastInsertId();
    }

    public function update(int $doctorId, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE doctor_info
            SET specialization = :specialization,
                license_number = :license_number,
                years_of_experience = :years_of_experience,
                consultation_fees = :consultation_fees
            WHERE doctor_id = :doctor_id"
        );

        $stmt->execute([
            'doctor_id' => $doctorId,
            'specialization' => $data['specialization'],
            'license_number' => $data['license_number'],
            'years_of_experience' => $data['years_of_experience'],
            'consultation_fees' => $data['consultation_fees'],
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $doctorId): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM doctor_info
            WHERE doctor_id = :doctor_id"
        );

        $stmt->execute(['doctor_id' => $doctorId]);

        return $stmt->rowCount() > 0;
    }
}