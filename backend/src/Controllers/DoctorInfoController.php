<?php

namespace src\Controllers;

use src\Helpers\Controller;
use src\Middleware\DoctorMiddleware;
use src\Models\User;
use src\Models\Doctor;
use src\Models\DoctorInfo;

class DoctorInfoController extends Controller
{
    public function getDoctorInfo(): void
    {
        DoctorMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $doctorInfoModel = new DoctorInfo();
        $doctorInfo = $doctorInfoModel->getDoctorInfoById($userId);

        if (!$doctorInfo) {
            $this->json([
                'message' => 'Doctor info not found.'
            ], 404);

            return;
        }

        $this->json([
            'doctor_info' => $doctorInfo
        ]);
    }

    public function addDoctorInfo(): void
    {
        DoctorMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        $doctorModel = new Doctor();
        $doctor = $doctorModel->getDoctorById($userId);
        $doctorId = $doctorModel->getDoctorIdByUserId($userId);


        if (!$doctorId) {
            $this->json([
                'message' => 'Doctor profile not found. Please create a doctor profile first.'
            ], 404);

            return;
        }

        $doctorInfoModel = new DoctorInfo();

        $data = [
            'doctor_id' => $doctorId,
            'specialization' => $payload['specialization'] ?? '',
            'license_number' => $payload['license_number'] ?? '',
            'years_of_experience' => $payload['years_of_experience'] ?? 0,
            'consultation_fees' => $payload['consultation_fees'] ?? 0,
        ];

        $doctorInfoId = $doctorInfoModel->create($data);

        if ($doctorInfoId) {
            $this->json([
                'message' => 'Doctor info created successfully.',
                'doctor_info_id' => $doctorInfoId
            ], 201);
        } else {
            $this->json([
                'message' => 'Failed to create doctor info.'
            ], 500);
        }
    }

    public function editDoctorInfo(): void
    {
        DoctorMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        $doctorModel = new Doctor();
        $doctor = $doctorModel->getDoctorById($userId);

        if (!$doctor) {
            $this->json([
                'message' => 'Doctor profile not found. Please create a doctor profile first.'
            ], 404);

            return;
        }

        $doctorInfoModel = new DoctorInfo();
        $doctorInfo = $doctorInfoModel->getDoctorInfoById($userId);

        if (!$doctorInfo) {
            $this->json([
                'message' => 'Doctor info not found. Please create doctor info first.'
            ], 404);

            return;
        }

        $data = [
            'specialization' => $payload['specialization'] ?? $doctorInfo['specialization'],
            'license_number' => $payload['license_number'] ?? $doctorInfo['license_number'],
            'years_of_experience' => $payload['years_of_experience'] ?? $doctorInfo['years_of_experience'],
            'consultation_fees' => $payload['consultation_fees'] ?? $doctorInfo['consultation_fees'],
        ];

        $success = $doctorInfoModel->update($doctorInfo['doctor_id'], $data);

        if ($success) {
            $this->json([
                'message' => 'Doctor info updated successfully.',
                'doctor_info' => $doctorInfoModel->getDoctorInfoById($userId)
            ]);
        } else {
            $this->json([
                'message' => 'Failed to update doctor info.'
            ], 500);
        }
    }

    public function deleteDoctorInfo(): void
    {
        DoctorMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $doctorModel = new Doctor();
        $doctor = $doctorModel->getDoctorById($userId);

        if (!$doctor) {
            $this->json([
                'message' => 'Doctor profile not found. Please create a doctor profile first.'
            ], 404);

            return;
        }

        $doctorInfoModel = new DoctorInfo();
        $doctorInfo = $doctorInfoModel->getDoctorInfoById($userId);

        if (!$doctorInfo) {
            $this->json([
                'message' => 'Doctor info not found.'
            ], 404);

            return;
        }

        $success = $doctorInfoModel->delete($doctorInfo['doctor_id']);

        if ($success) {
            $this->json([
                'message' => 'Doctor info deleted successfully.'
            ]);
        } else {
            $this->json([
                'message' => 'Failed to delete doctor info.'
            ], 500);
        }
    }
}