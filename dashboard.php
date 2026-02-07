<?php
require_once 'config.php';
require_once 'src/Database.php';
require_once 'src/SessionManager.php';
require_once 'src/RateLimiter.php';
require_once 'src/AuthService.php';

$authService = new AuthService();
$authService->requireLogin();

$currentUser = $authService->getCurrentUser();
$db = Database::getInstance();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {},
            },
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 font-sans min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-primary text-primary-content shadow-md">
        <div class="container mx-auto flex justify-between items-center p-4">
            <h1 class="text-xl font-bold">Dashboard</h1>
            <div class="flex items-center gap-4">
                <span>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?>!</span>
                <a href="logout.php" class="btn btn-error btn-sm">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 container mx-auto p-4">
        <div class="bg-base-100 shadow-lg rounded-lg p-6">
            <div class="mb-6 text-lg text-base-content">
                Welcome to dashboard. You are successfully logged in as 
                <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong> 
                (<?php echo htmlspecialchars($currentUser['role']); ?>).
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div class="card bg-primary/10 shadow">
                    <div class="card-body">
                        <h2 class="card-title">Total Users</h2>
                        <p class="text-3xl font-bold">
                            <?php
                            $result = $db->query("SELECT COUNT(*) as count FROM users");
                            $row = $result->fetch_assoc();
                            echo $row['count'];
                            ?>
                        </p>
                    </div>
                </div>
                <div class="card bg-primary/10 shadow">
                    <div class="card-body">
                        <h2 class="card-title">Server Time</h2>
                        <p class="text-3xl font-bold"><?php echo date('H:i:s'); ?></p>
                    </div>
                </div>
                <div class="card bg-primary/10 shadow">
                    <div class="card-body">
                        <h2 class="card-title">Login Status</h2>
                        <p class="text-3xl font-bold">Active</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
