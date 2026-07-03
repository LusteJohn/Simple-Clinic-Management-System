<?php 

namespace src\Middleware;

class AdminMiddleware
{
    public static function handle(): void
    {
        if (
            !isset($_SESSION['user']) ||
            $_SESSION['user']['role'] !== 'admin'
        ) {
            http_response_code(403);

            echo json_encode([
                'message' => 'Unauthorized.',
            ]);

            exit;
        }
    }
}

?>