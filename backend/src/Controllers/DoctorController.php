<?php 

namespace src\Controllers;

use src\Helpers\Controller;
use src\Middleware\DoctorMiddleware;
use src\Models\User;
use src\Models\Doctor;

class DoctorController extends Controller
{
    public function getDoctorProfile(): void
    {
        DoctorMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $userModel = new User();
        $doctorModel = new Doctor();

        $user = $userModel->findById($userId);
        $doctor = $doctorModel->getDoctorById($userId);

        if (!$user || !$doctor) {
            $this->json([
                'message' => 'Doctor profile not found.'
            ], 404);

            return;
        }

        $this->json([
            'user' => $user,
            'doctor' => $doctor
        ]);
    }

    public function addDoctorProfile(): void
    {
        DoctorMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];;

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        $doctorModel = new Doctor();

        $data = [
            'user_id' => $userId,
            'firstname' => $payload['firstname'] ?? '',
            'middlename' => $payload['middlename'] ?? '',
            'lastname' => $payload['lastname'] ?? '',
            'name_ext' => $payload['name_ext'] ?? '',
            'gender' => $payload['gender'] ?? '',
        ];

        $doctor = $doctorModel->getDoctorById($userId);

        if ($doctor) {
            $this->json([
                'message' => 'Doctor profile already exists.'
            ], 409);

            return;
        }

        $doctorId = $doctorModel->create($data);

        $this->json([
            'message' => 'Doctor profile created successfully.',
            'doctor_id' => $doctorId
        ], 201);
    }

    public function editDoctorProfile(): void
    {
        DoctorMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        $doctorModel = new Doctor();

        $doctor = $doctorModel->getDoctorById($userId);

        if (!$doctor) {
            $this->json([
                'message' => 'Doctor profile not found.'
            ], 404);

            return;
        }

        $data = [
            'firstname' => $payload['firstname'] ?? $doctor['firstname'],
            'middlename' => $payload['middlename'] ?? $doctor['middlename'],
            'lastname' => $payload['lastname'] ?? $doctor['lastname'],
            'name_ext' => $payload['name_ext'] ?? $doctor['name_ext'],
            'gender' => $payload['gender'] ?? $doctor['gender'],
        ];

        $success = $doctorModel->update($userId, $data);

        if ($success) {
            $this->json([
                'message' => 'Doctor profile updated successfully.',
                'doctor' => $doctorModel->getDoctorById($userId)
            ]);
        } else {
            $this->json([
                'message' => 'Failed to update doctor profile.'
            ], 500);
        }
    }

    public function deleteDoctorProfile(): void
    {
        DoctorMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $doctorModel = new Doctor();

        $doctor = $doctorModel->getDoctorById($userId);

        if (!$doctor) {
            $this->json([
                'message' => 'Doctor profile not found.'
            ], 404);

            return;
        }

        // Assuming you have a method to delete the doctor profile
        $success = $doctorModel->delete($userId);

        if ($success) {
            $this->json([
                'message' => 'Doctor profile deleted successfully.'
            ]);
        } else {
            $this->json([
                'message' => 'Failed to delete doctor profile.'
            ], 500);
        }
    }
}
