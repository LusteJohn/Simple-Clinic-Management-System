<?php

namespace src\Controllers;

use DateTime;
use src\Helpers\Controller;
use src\Middleware\PatientMiddleware;
use src\Models\Appointment;
use src\Models\DoctorSchedule;
use src\Models\Patient;

class AppointmentController extends Controller
{
    private function getPayload(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function normalizeDate(?string $date): string|false
    {
        if (!$date) {
            return false;
        }

        $parsed = DateTime::createFromFormat('Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date ? $date : false;
    }

    private function normalizeTime(?string $time): string|false
    {
        if (!$time) {
            return false;
        }

        foreach (['H:i:s', 'H:i'] as $format) {
            $parsed = DateTime::createFromFormat($format, $time);

            if ($parsed && $parsed->format($format) === $time) {
                return $parsed->format('H:i:s');
            }
        }

        return false;
    }

    private function getPatientIdOrFail(): int|false
    {
        $userId = (int) ($_SESSION['user']['user_id'] ?? 0);

        if ($userId <= 0) {
            return false;
        }

        $patientModel = new Patient();

        return $patientModel->getPatientIdByUserId($userId);
    }

    public function getMyAppointments(): void
    {
        PatientMiddleware::handle();

        $patientId = $this->getPatientIdOrFail();

        if (!$patientId) {
            $this->json([
                'message' => 'Patient profile not found.',
            ], 404);

            return;
        }

        $appointmentModel = new Appointment();

        $this->json([
            'appointments' => $appointmentModel->getAppointmentsByPatientId($patientId),
        ]);
    }

    public function getAppointmentById(int $appointmentId): void
    {
        PatientMiddleware::handle();

        $patientId = $this->getPatientIdOrFail();

        if (!$patientId) {
            $this->json([
                'message' => 'Patient profile not found.',
            ], 404);

            return;
        }

        $appointmentModel = new Appointment();
        $appointment = $appointmentModel->getAppointmentById($appointmentId, $patientId);

        if (!$appointment) {
            $this->json([
                'message' => 'Appointment not found.',
            ], 404);

            return;
        }

        $this->json([
            'appointment' => $appointment,
        ]);
    }

    public function createAppointment(): void
    {
        PatientMiddleware::handle();

        $patientId = $this->getPatientIdOrFail();

        if (!$patientId) {
            $this->json([
                'message' => 'Patient profile not found. Please complete your patient profile first.',
            ], 404);

            return;
        }

        $payload = $this->getPayload();

        $doctorId = (int) ($payload['doctor_id'] ?? 0);
        $appointmentDate = $this->normalizeDate($payload['appointment_date'] ?? null);
        $appointmentTime = $this->normalizeTime($payload['appointment_time'] ?? null);
        $reasonForVisit = trim((string) ($payload['reason_for_visit'] ?? ''));

        if ($doctorId <= 0 || !$appointmentDate || !$appointmentTime) {
            $this->json([
                'message' => 'doctor_id, appointment_date, and appointment_time are required.',
            ], 422);

            return;
        }

        $scheduleModel = new DoctorSchedule();

        if (!$scheduleModel->isBookable($doctorId, $appointmentDate, $appointmentTime)) {
            $this->json([
                'message' => 'This doctor is not available for booking at the selected date and time.',
            ], 409);

            return;
        }

        $appointmentModel = new Appointment();

        if ($appointmentModel->findByDoctorDateTime($doctorId, $appointmentDate, $appointmentTime)) {
            $this->json([
                'message' => 'This appointment slot is already booked.',
            ], 409);

            return;
        }

        try {
            $appointmentId = $appointmentModel->create([
                'doctor_id' => $doctorId,
                'patient_id' => $patientId,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'reason_for_visit' => $reasonForVisit !== '' ? $reasonForVisit : null,
                'status' => 'pending',
                'booked_by' => 'patient',
            ]);
        } catch (\Throwable $exception) {
            $this->json([
                'message' => 'Failed to create appointment.',
            ], 500);

            return;
        }

        $this->json([
            'message' => 'Appointment created successfully.',
            'appointment_id' => $appointmentId,
        ], 201);
    }

    public function updateAppointment(int $appointmentId): void
    {
        PatientMiddleware::handle();

        $patientId = $this->getPatientIdOrFail();

        if (!$patientId) {
            $this->json([
                'message' => 'Patient profile not found.',
            ], 404);

            return;
        }

        $appointmentModel = new Appointment();
        $existingAppointment = $appointmentModel->getAppointmentById($appointmentId, $patientId);

        if (!$existingAppointment) {
            $this->json([
                'message' => 'Appointment not found.',
            ], 404);

            return;
        }

        $payload = $this->getPayload();

        $doctorId = (int) ($payload['doctor_id'] ?? $existingAppointment['doctor_id']);
        $appointmentDate = $this->normalizeDate($payload['appointment_date'] ?? $existingAppointment['appointment_date']);
        $appointmentTime = $this->normalizeTime($payload['appointment_time'] ?? $existingAppointment['appointment_time']);
        $reasonForVisit = array_key_exists('reason_for_visit', $payload)
            ? trim((string) $payload['reason_for_visit'])
            : (string) ($existingAppointment['reason_for_visit'] ?? '');

        if ($doctorId <= 0 || !$appointmentDate || !$appointmentTime) {
            $this->json([
                'message' => 'doctor_id, appointment_date, and appointment_time are required.',
            ], 422);

            return;
        }

        $scheduleModel = new DoctorSchedule();

        if (!$scheduleModel->isBookable($doctorId, $appointmentDate, $appointmentTime)) {
            $this->json([
                'message' => 'This doctor is not available for booking at the selected date and time.',
            ], 409);

            return;
        }

        $duplicateAppointment = $appointmentModel->findByDoctorDateTime($doctorId, $appointmentDate, $appointmentTime);

        if ($duplicateAppointment && (int) $duplicateAppointment['appointment_id'] !== $appointmentId) {
            $this->json([
                'message' => 'This appointment slot is already booked.',
            ], 409);

            return;
        }

        $success = $appointmentModel->update($appointmentId, $patientId, [
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
            'reason_for_visit' => $reasonForVisit,
            'status' => $payload['status'] ?? $existingAppointment['status'],
        ]);

        if ($success) {
            $this->json([
                'message' => 'Appointment updated successfully.',
            ]);

            return;
        }

        $this->json([
            'message' => 'Failed to update appointment.',
        ], 500);
    }

    public function deleteAppointment(int $appointmentId): void
    {
        PatientMiddleware::handle();

        $patientId = $this->getPatientIdOrFail();

        if (!$patientId) {
            $this->json([
                'message' => 'Patient profile not found.',
            ], 404);

            return;
        }

        $appointmentModel = new Appointment();

        if (!$appointmentModel->getAppointmentById($appointmentId, $patientId)) {
            $this->json([
                'message' => 'Appointment not found.',
            ], 404);

            return;
        }

        $success = $appointmentModel->delete($appointmentId, $patientId);

        if ($success) {
            $this->json([
                'message' => 'Appointment deleted successfully.',
            ]);

            return;
        }

        $this->json([
            'message' => 'Failed to delete appointment.',
        ], 500);
    }
}
