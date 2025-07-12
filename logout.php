<?php
/**
 * =================================================================
 * SECURE LOGOUT SCRIPT
 * =================================================================
 * This script handles user logout with CSRF protection.
 *
 * SECURITY BEST PRACTICES:
 * - Session started securely.
 * - Only accepts POST requests to prevent CSRF via GET.
 * - Validates a CSRF token to ensure the request originates from the application.
 * - Uses hash_equals for timing-attack-safe token comparison.
 * =================================================================
 */

require_once 'services/AuthService.php';
$authService = new AuthService();

// Start the session to access session variables
$authService->startSession();

// 1. Verify the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If not POST, redirect to home page. This prevents direct URL access.
    header('Location: index.php');
    exit();
}

// 2. Validate the CSRF token
$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (empty($submittedToken) || empty($sessionToken) || !hash_equals($sessionToken, $submittedToken)) {
    // Token mismatch or missing, abort and redirect.
    // Optionally, log this attempt as a potential security event.
    header('Location: index.php');
    exit();
}

// 3. If all checks pass, proceed with logout
if ($authService->isLoggedIn()) {
    $authService->logout(); // This should handle session destruction.
}

// 4. Unset the CSRF token just in case
unset($_SESSION['csrf_token']);

// 5. Redirect to the login page with a success message
header('Location: login.php?logout=success');
exit();