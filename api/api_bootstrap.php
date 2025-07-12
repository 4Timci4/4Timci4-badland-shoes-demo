<?php



require_once __DIR__ . '/../config/session.php';


require_once __DIR__ . '/../services/AuthService.php';


$authService = new AuthService();


$authService->checkSessionSecurity();


$is_logged_in = $authService->isLoggedIn();
$current_user = $is_logged_in ? $authService->getCurrentUser() : null;


function api_success($data = [], $message = 'Success')
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function api_error($message = 'Error', $code = 400)
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}


function api_require_login()
{
    global $is_logged_in;

    if (!$is_logged_in) {
        api_error('Unauthorized', 401);
    }
}