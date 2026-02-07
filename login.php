<?php
require_once 'config.php';
require_once 'src/Database.php';
require_once 'src/SessionManager.php';
require_once 'src/RateLimiter.php';
require_once 'src/AuthService.php';

$authService = new AuthService();

// Redirect if already logged in
if ($authService->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$clientIp = $_SERVER['REMOTE_ADDR'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $authService->login($username, $password, $clientIp);
    
    if ($result['success']) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = $result['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
    <script type="module" src="https://cdn.jsdelivr.net/npm/lucide@0.264.0/dist/lucide.esm.js"></script>
</head>
<body class="bg-base-200 flex items-center justify-center min-h-screen">

    <div class="card w-full max-w-md shadow-xl bg-base-100 p-6">
        <h2 class="text-2xl font-bold text-center mb-6">Admin Login</h2>

        <?php if ($error): ?>
            <div class="alert alert-error mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Username</span>
                </label>
                <div class="input-group">
                    <span class="bg-base-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="lucide lucide-user w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </span>
                    <input type="text" placeholder="Username" name="username" class="input input-bordered w-full" required>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Password</span>
                </label>
                <div class="input-group">
                    <span class="bg-base-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="lucide lucide-lock w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input type="password" placeholder="Password" name="password" class="input input-bordered w-full" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full">Login</button>
        </form>
    </div>

</body>
</html>
