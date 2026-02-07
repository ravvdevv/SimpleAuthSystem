<?php
require_once 'config.php';
require_once 'src/Database.php';
require_once 'src/SessionManager.php';
require_once 'src/RateLimiter.php';
require_once 'src/AuthService.php';

$authService = new AuthService();

// Check if user is already logged in
if ($authService->isLoggedIn()) {
    // Redirect to appropriate dashboard based on role
    $currentUser = $authService->getCurrentUser();
    if ($authService->isAdmin()) {
        header('Location: dashboard.php');
    } else {
        // For future: could redirect to user dashboard
        header('Location: dashboard.php');
    }
    exit;
} else {
    // Redirect to login page
    header('Location: login.php');
    exit;
}
?>
