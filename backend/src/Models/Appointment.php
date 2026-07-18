<?php

namespace src\Models;

use src\Config\Database;

class Appointment {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAppointmentsByPatientId(int $patientId): array {
        $stmt = $this->db->prepare("
            SELECT
                a.appointment_id,
                a.doctor_id,
                a.patient_id,
                CONCAT(d.firstname, ' ', d.lastname) AS doctor_name,
                a.appointment_date,
                a.appointment_time,
                a.status,
                a.reason_for_visit,
                a.booked_by,
                a.created_at,
                a.updated_at
            FROM appointments a
            INNER JOIN doctors d ON a.doctor_id = d.doctor_id
            WHERE a.patient_id = :patient_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");

        $stmt->execute([
            'patient_id' => $patientId,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAppointmentById(int $appointmentId, int $patientId): array|false {
        $stmt = $this->db->prepare("
            SELECT
                a.appointment_id,
                a.doctor_id,
                a.patient_id,
                CONCAT(d.firstname, ' ', d.lastname) AS doctor_name,
                a.appointment_date,
                a.appointment_time,
                a.status,
                a.reason_for_visit,
                a.booked_by,
                a.created_at,
                a.updated_at
            FROM appointments a
            INNER JOIN doctors d ON a.doctor_id = d.doctor_id
            WHERE a.appointment_id = :appointment_id
              AND a.patient_id = :patient_id
            LIMIT 1
        ");

        $stmt->execute([
            'appointment_id' => $appointmentId,
            'patient_id' => $patientId,
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByDoctorDateTime(int $doctorId, string $appointmentDate, string $appointmentTime): array|false {
        $stmt = $this->db->prepare("
            SELECT
                appointment_id,
                doctor_id,
                patient_id,
                appointment_date,
                appointment_time,
                status,
                reason_for_visit,
                booked_by,
                created_at,
                updated_at
            FROM appointments
            WHERE doctor_id = :doctor_id
              AND appointment_date = :appointment_date
              AND appointment_time = :appointment_time
            LIMIT 1
        ");

        $stmt->execute([
            'doctor_id' => $doctorId,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO appointments (doctor_id, patient_id, appointment_date, appointment_time, status, reason_for_visit, booked_by, created_at, updated_at)
            VALUES (:doctor_id, :patient_id, :appointment_date, :appointment_time, :status, :reason_for_visit, :booked_by, NOW(), NOW())
        ");

        $stmt->execute([
            'doctor_id' => $data['doctor_id'],
            'patient_id' => $data['patient_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'status' => $data['status'] ?? 'pending',
            'reason_for_visit' => $data['reason_for_visit'] ?? null,
            'booked_by' => $data['booked_by'] ?? 'patient',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $appointmentId, int $patientId, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE appointments
            SET appointment_date = :appointment_date,
                appointment_time = :appointment_time,
                reason_for_visit = :reason_for_visit,
                status = :status
            WHERE appointment_id = :appointment_id
              AND patient_id = :patient_id
        ");

        $stmt->execute([
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'reason_for_visit' => $data['reason_for_visit'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'appointment_id' => $appointmentId,
            'patient_id' => $patientId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $appointmentId, int $patientId): bool {
        $stmt = $this->db->prepare("
            DELETE FROM appointments
            WHERE appointment_id = :appointment_id
              AND patient_id = :patient_id
        ");

        $stmt->execute([
            'appointment_id' => $appointmentId,
            'patient_id' => $patientId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
