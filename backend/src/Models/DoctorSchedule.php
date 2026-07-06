<?php

namespace src\Models;

use src\Config\Database;

class DoctorSchedule {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getDoctorScheduleById(int $userId): array|false {
        $stmt = $this->db->prepare("
            SELECT
                d.user_id,
                ds.schedule_id,
                ds.doctor_id,
                ds.day_of_week,
                ds.start_time,
                ds.end_time,
                ds.slot_duration_min,
                ds.is_active
            FROM doctor_schedules ds
            INNER JOIN doctors d ON ds.doctor_id = d.doctor_id
            WHERE d.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, slot_duration_min, is_active, created_at)
            VALUES (:doctor_id, :day_of_week, :start_time, :end_time, :slot_duration_min, :is_active, NOW())"
        );

        $success = $stmt->execute([
            'doctor_id' => $data['doctor_id'],
            'day_of_week' => $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'slot_duration_min' => $data['slot_duration_min'],
            'is_active' => $data['is_active'] ?? 1,
        ]);

        if (!$success) {
            die(print_r($stmt->errorInfo(), true));
        }

        return (int)$this->db->lastInsertId();
    }

    public function update(int $scheduleId, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE doctor_schedules
            SET day_of_week = :day_of_week,
                start_time = :start_time,
                end_time = :end_time,
                slot_duration_min = :slot_duration_min,
                is_active = :is_active
            WHERE schedule_id = :schedule_id"
        );

        return $stmt->execute([
            'day_of_week' => $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'slot_duration_min' => $data['slot_duration_min'],
            'is_active' => $data['is_active'] ?? 'active',
            'schedule_id' => $scheduleId,
        ]);
    }

    public function delete(int $scheduleId): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM doctor_schedules
            WHERE schedule_id = :schedule_id"
        );
        $stmt->execute(['schedule_id' => $scheduleId]);

        return $stmt->rowCount() > 0;
    }
}