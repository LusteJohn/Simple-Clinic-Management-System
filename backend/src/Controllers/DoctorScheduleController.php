<?php

namespace src\Controllers;

use src\Helpers\Controller;
use src\Middleware\DoctorMiddleware;
use src\Models\User;
use src\Models\Doctor;
use src\Models\DoctorSchedule;

class DoctorScheduleController extends Controller
{
    public function getDoctorSchedule(): void
    {
        DoctorMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $doctorScheduleModel = new DoctorSchedule();
        $doctorSchedule = $doctorScheduleModel->getDoctorScheduleById($userId);

        if (!$doctorSchedule) {
            $this->json([
                'message' => 'Doctor schedule not found.'
            ], 404);

            return;
        }

        $this->json([
            'doctor_schedule' => $doctorSchedule
        ]);
    }

    public function addDoctorSchedule(): void
    {
        DoctorMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        $doctorModel = new Doctor();
        $doctorId = $doctorModel->getDoctorIdByUserId($userId);

        if (!$doctorId) {
            $this->json([
                'message' => 'Doctor profile not found. Please create a doctor profile first.'
            ], 404);

            return;
        }

        $doctorScheduleModel = new DoctorSchedule();

        $data = [
            'doctor_id' => $doctorId,
            'day_of_week' => $payload['day_of_week'] ?? '',
            'start_time' => $payload['start_time'] ?? '',
            'end_time' => $payload['end_time'] ?? '',
            'slot_duration_min' => $payload['slot_duration_min'] ?? 15,
            'is_active' => $payload['is_active'] ?? 'active',
        ];

        $scheduleId = $doctorScheduleModel->create($data);

        if (!$scheduleId) {
            $this->json([
                'message' => 'Failed to create doctor schedule.'
            ], 500);

            return;
        }

        $this->json([
            'message' => 'Doctor schedule created successfully.',
            'schedule_id' => $scheduleId
        ], 201);
    }

    public function editDoctorSchedule(): void
    {
        DoctorMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        $doctorModel = new Doctor();
        $doctorId = $doctorModel->getDoctorIdByUserId($userId);

        if (!$doctorId) {
            $this->json([
                'message' => 'Doctor profile not found. Please create a doctor profile first.'
            ], 404);

            return;
        }

        $doctorScheduleModel = new DoctorSchedule();

        $scheduleId = $payload['schedule_id'] ?? null;

        if (!$scheduleId) {
            $this->json([
                'message' => 'Schedule ID is required for updating the schedule.'
            ], 400);

            return;
        }

        $data = [
            'day_of_week' => $payload['day_of_week'] ?? '',
            'start_time' => $payload['start_time'] ?? '',
            'end_time' => $payload['end_time'] ?? '',
            'slot_duration_min' => $payload['slot_duration_min'] ?? 15,
            'is_active' => $payload['is_active'] ?? 'active',
        ];

        $success = $doctorScheduleModel->update($scheduleId, $data);

        if ($success) {
            $this->json([
                'message' => 'Doctor schedule updated successfully.'
            ]);
        } else {
            $this->json([
                'message' => 'Failed to update doctor schedule.'
            ], 500);
        }
    }

    public function deleteDoctorSchedule(): void
    {
        DoctorMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        $doctorModel = new Doctor();
        $doctorId = $doctorModel->getDoctorIdByUserId($userId);

        if (!$doctorId) {
            $this->json([
                'message' => 'Doctor profile not found. Please create a doctor profile first.'
            ], 404);

            return;
        }

        $doctorScheduleModel = new DoctorSchedule();

        $scheduleId = $payload['schedule_id'] ?? null;

        if (!$scheduleId) {
            $this->json([
                'message' => 'Schedule ID is required for deleting the schedule.'
            ], 400);

            return;
        }

        $success = $doctorScheduleModel->delete($scheduleId);

        if ($success) {
            $this->json([
                'message' => 'Doctor schedule deleted successfully.'
            ]);
        } else {
            $this->json([
                'message' => 'Failed to delete doctor schedule.'
            ], 500);
        }
    }
}