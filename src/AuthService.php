<?php
/**
 * Authentication service
 */
class AuthService {
    private $db;
    private $sessionManager;
    private $rateLimiter;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->sessionManager = new SessionManager();
        $this->rateLimiter = new RateLimiter();
    }
    
    public function login($username, $password, $clientIp) {
        // Check rate limiting
        if ($this->rateLimiter->isRateLimited($clientIp)) {
            $remainingTime = $this->rateLimiter->getRemainingLockoutTime($clientIp);
            $minutes = ceil($remainingTime / 60);
            return [
                'success' => false,
                'error' => "Too many login attempts. Please try again in {$minutes} minute(s)."
            ];
        }
        
        // Validate input
        if (empty($username) || empty($password)) {
            $this->rateLimiter->recordAttempt($clientIp);
            return [
                'success' => false,
                'error' => 'Please enter both username and password'
            ];
        }
        
        // Prepare statement - updated to use 'users' table
        $stmt = $this->db->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Successful login
                $this->sessionManager->login($user['id'], $user['username'], $user['role']);
                $this->rateLimiter->clearAttempts($clientIp);
                
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'role' => $user['role']
                    ]
                ];
            } else {
                $this->rateLimiter->recordAttempt($clientIp);
                return [
                    'success' => false,
                    'error' => 'Invalid password'
                ];
            }
        } else {
            $this->rateLimiter->recordAttempt($clientIp);
            return [
                'success' => false,
                'error' => 'Invalid username'
            ];
        }
        
        $stmt->close();
    }
    
    public function logout() {
        $this->sessionManager->logout();
    }
    
    public function isLoggedIn() {
        return $this->sessionManager->isLoggedIn();
    }
    
    public function getCurrentUser() {
        return $this->sessionManager->getCurrentUser();
    }
    
    public function hasRole($role) {
        return $this->sessionManager->hasRole($role);
    }
    
    public function isAdmin() {
        return $this->sessionManager->isAdmin();
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public function requireAdmin() {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public function createUser($username, $password, $role = 'user') {
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'error' => 'Username and password are required'
            ];
        }
        
        // Validate role
        if (!in_array($role, ['admin', 'user'])) {
            return [
                'success' => false,
                'error' => 'Invalid role. Must be admin or user'
            ];
        }
        
        // Check if user already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return [
                'success' => false,
                'error' => 'Username already exists'
            ];
        }
        
        // Create new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashedPassword, $role);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'user_id' => $this->db->getLastInsertId()
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to create user'
            ];
        }
        
        $stmt->close();
    }
    
    public function validatePassword($password) {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one number';
        }
        
        return true;
    }
}
?>
