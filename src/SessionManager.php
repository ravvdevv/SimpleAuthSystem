<?php
/**
 * Session management class
 */
class SessionManager {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    public function destroy() {
        session_unset();
        session_destroy();
    }
    
    public function regenerateId() {
        session_regenerate_id(true);
    }
    
    public function isLoggedIn() {
        return $this->get('user_logged_in') === true;
    }
    
    public function login($userId, $username, $role = 'user') {
        $this->set('user_logged_in', true);
        $this->set('user_id', $userId);
        $this->set('user_username', $username);
        $this->set('user_role', $role);
        $this->regenerateId();
    }
    
    public function logout() {
        $this->destroy();
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $this->get('user_id'),
                'username' => $this->get('user_username'),
                'role' => $this->get('user_role')
            ];
        }
        return null;
    }
    
    public function hasRole($role) {
        $currentUser = $this->getCurrentUser();
        return $currentUser && $currentUser['role'] === $role;
    }
    
    public function isAdmin() {
        return $this->hasRole('admin');
    }
}
?>
