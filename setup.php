<?php
require_once 'config.php';
require_once 'src/Database.php';

$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Check if already installed
if (file_exists('installed.lock')) {
    $error = 'System is already installed. To reinstall, delete the installed.lock file.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$error) {
    try {
        $db = Database::getInstance();
        
        if ($step == 1) {
            // Create database
            $db->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
            $success = 'Database created successfully!';
            $step = 2;
        } elseif ($step == 2) {
            // Create users table
            $createTableSQL = "
                CREATE TABLE IF NOT EXISTS users (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    role ENUM('admin', 'user') NOT NULL,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";
            $db->query($createTableSQL);
            
            // Insert default admin user
            $adminPassword = password_hash('password', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (role, username, password) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE username=username");
            $stmt->bind_param("sss", $role, $username, $adminPassword);
            $role = 'admin';
            $username = 'admin';
            $stmt->execute();
            $stmt->close();
            
            // Create installation lock file
            file_put_contents('installed.lock', date('Y-m-d H:i:s'));
            
            $success = 'Installation completed successfully! Default admin: admin/password';
            $step = 3;
        }
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleAuth Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .setup-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }
        .step {
            background: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .config-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .progress-step {
            flex: 1;
            text-align: center;
            padding: 0.5rem;
            background: #e9ecef;
            border-radius: 4px;
            margin: 0 2px;
        }
        .progress-step.active {
            background: #007bff;
            color: white;
        }
        .progress-step.completed {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>SimpleAuth Setup</h1>
        
        <div class="progress">
            <div class="progress-step <?php echo $step >= 1 ? 'completed' : ''; ?>">1. Database</div>
            <div class="progress-step <?php echo $step >= 2 ? ($step == 2 ? 'active' : 'completed') : ''; ?>">2. Tables</div>
            <div class="progress-step <?php echo $step >= 3 ? 'completed' : ''; ?>">3. Complete</div>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($step < 3 && !$error): ?>
            <div class="config-info">
                <strong>Database Configuration:</strong><br>
                Host: <?php echo DB_HOST; ?><br>
                Database: <?php echo DB_NAME; ?><br>
                User: <?php echo DB_USER; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($step == 1 && !$error): ?>
            <div class="step">Step 1: Create Database</div>
            <p>This step will create the database "<?php echo DB_NAME; ?>" if it doesn't exist.</p>
            
            <form method="POST" action="">
                <button type="submit">Create Database</button>
            </form>
        <?php endif; ?>
        
        <?php if ($step == 2 && !$error): ?>
            <div class="step">Step 2: Create Tables & Default User</div>
            <p>This step will create the users table and insert a default admin user.</p>
            
            <form method="POST" action="">
                <button type="submit">Create Tables & Default User</button>
            </form>
        <?php endif; ?>
        
        <?php if ($step == 3): ?>
            <div class="step">Installation Complete!</div>
            <p>Your SimpleAuth system is now ready to use.</p>
            
            <div class="config-info">
                <strong>Default Login:</strong><br>
                Username: admin<br>
                Password: password<br><br>
                <strong>Important:</strong> Change this password after first login!
            </div>
            
            <p><a href="index.php" style="color: #007bff;">Go to Login Page</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
