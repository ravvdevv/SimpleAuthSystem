<?php
require_once 'config.php';
require_once 'src/Database.php';
require_once 'src/SessionManager.php';
require_once 'src/RateLimiter.php';
require_once 'src/AuthService.php';

$authService = new AuthService();
$authService->logout();

// Redirect to login page
header('Location: login.php');
exit;
?>
