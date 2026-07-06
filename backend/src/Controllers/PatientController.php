<?php 

namespace src\Controllers;

use src\Helpers\Controller;
use src\Middleware\PatientMiddleware;
use src\Middleware\AdminMiddleware;
use src\Models\User;
use src\Models\Patient;

class PatientController extends Controller
{
    public function getAllPatients(): void
    {
        AdminMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $patientModel = new Patient();
        $patients = $patientModel->getAllPatientByAdmin();

        $this->json([
            'patients' => $patients
        ]);
    }

    public function getPatientProfile(): void
    {
        PatientMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $userModel = new User();
        $patientModel = new Patient();

        $user = $userModel->findById($userId);
        $patient = $patientModel->getPatientById($userId);

        if (!$user || !$patient) {
            $this->json([
                'message' => 'Patient profile not found.'
            ], 404);

            return;
        }

        $this->json([
            'user' => $user,
            'patient' => $patient
        ]);
    }

    public function addPatientProfile(): void
    {
        PatientMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];;

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        $patientModel = new Patient();

        $data = [
            'user_id' => $userId,
            'firstname' => $payload['firstname'] ?? '',
            'middlename' => $payload['middlename'] ?? '',
            'lastname' => $payload['lastname'] ?? '',
            'name_ext' => $payload['name_ext'] ?? '',
            'gender' => $payload['gender'] ?? '',
        ];

        $patient = $patientModel->getPatientById($userId);

        if ($patient) {
            $this->json([
                'message' => 'Patient profile already exists.'
            ], 409);

            return;
        }

        $patientId = $patientModel->create($data);

        $this->json([
            'message' => 'Patient profile created successfully.',
            'patient_id' => $patientId
        ], 201);
    }

    public function editPatientProfile(): void
    {
        PatientMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];

        $patientModel = new Patient();

        $patient = $patientModel->getPatientById($userId);

        if (!$patient) {
            $this->json([
                'message' => 'Patient profile not found.'
            ], 404);

            return;
        }

        $data = [
            'firstname' => $payload['firstname'] ?? $patient['firstname'],
            'middlename' => $payload['middlename'] ?? $patient['middlename'],
            'lastname' => $payload['lastname'] ?? $patient['lastname'],
            'name_ext' => $payload['name_ext'] ?? $patient['name_ext'],
            'gender' => $payload['gender'] ?? $patient['gender'],
        ];

        $success = $patientModel->update($userId, $data);

        if ($success) {
            $this->json([
                'message' => 'Patient profile updated successfully.',
                'patient' => $patientModel->getPatientById($userId)
            ]);
        } else {
            $this->json([
                'message' => 'Failed to update patient profile.'
            ], 500);
        }
    }

    public function deletePatientProfile(): void
    {
        PatientMiddleware::handle();

        $userId = $_SESSION['user']['user_id'];

        $patientModel = new Patient();

        $patient = $patientModel->getPatientById($userId);

        if (!$patient) {
            $this->json([
                'message' => 'Patient profile not found.'
            ], 404);

            return;
        }

        // Assuming you have a method to delete the patient profile
        $success = $patientModel->delete($userId);

        if ($success) {
            $this->json([
                'message' => 'Patient profile deleted successfully.'
            ]);
        } else {
            $this->json([
                'message' => 'Failed to delete patient profile.'
            ], 500);
        }
    }
}
