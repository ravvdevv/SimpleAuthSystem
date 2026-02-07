# Simple PHP Auth Boilerplate

A minimal PHP authentication boilerplate template for admin login/logout systems using MySQL.

## Features

- Simple username/password authentication
- Session management
- SQL injection prevention
- Basic admin dashboard template
- Logout functionality
- Clean, minimal design

## Quick Setup

### Method 1: Web-Based Setup (Recommended)

1. Place all files in your web server directory
2. Update database credentials in `config.php`
3. Access `setup.php` in your browser
4. Follow the step-by-step installation wizard
5. Login with default credentials (admin/admin123)

### Method 2: Manual Setup

1. Place all files in your web server directory
2. Update database credentials in `config.php`
3. Import the `setup.sql` file into your MySQL database:
```bash
mysql -u root -p < setup.sql
```
4. Access `index.php` in your browser
5. Login with default credentials

### 3. Default Login

- **Username:** admin
- **Password:** admin123

## File Structure

```
SimpleAuthSystem/
├── config.php              # Database configuration
├── setup.php               # Web-based installation wizard
├── setup.sql               # Database setup script (manual)
├── index.php               # Main entry point with smart redirection
├── src/                    # Core application classes
│   ├── Database.php        # Database abstraction layer (Singleton)
│   ├── SessionManager.php  # Session management class
│   ├── RateLimiter.php     # Rate limiting service
│   └── AuthService.php     # Main authentication service
├── login.php               # Login form and processing
├── dashboard.php           # Admin dashboard template
├── logout.php              # Logout functionality
└── README.md               # This file
```

## Modular Architecture

The system now follows a modular object-oriented design with clear separation of concerns:

### Core Components

**Database.php** - Singleton pattern for database connections
- Connection management
- Query preparation and execution
- Error handling

**SessionManager.php** - Session abstraction
- Secure session handling
- Login/logout state management
- Session regeneration for security

**RateLimiter.php** - Brute force protection
- IP-based attempt tracking
- Configurable limits and time windows
- Automatic cleanup of old attempts

**AuthService.php** - Main authentication logic
- User authentication with rate limiting
- Password validation and hashing
- User management functions
- Session integration

### Benefits of Modular Design

- **Maintainability:** Each class has a single responsibility
- **Testability:** Components can be unit tested independently
- **Extensibility:** Easy to add new features without modifying existing code
- **Reusability:** Components can be used in other projects
- **Security:** Centralized security logic reduces errors

## Security Features

- Password hashing using PHP's `password_hash()`
- Prepared statements to prevent SQL injection
- Session-based authentication
- Input sanitization
- **Rate limiting** to prevent brute force attacks (5 attempts per 5 minutes per IP)

## Usage

1. Place all files in your web server directory
2. Set up the database using `setup.sql`
3. Update database credentials in `config.php`
4. Access `index.php` in your browser (main entry point)
5. Login with default credentials
6. You'll be redirected to the admin dashboard

**Note:** `index.php` automatically redirects users:
- Not logged in → `login.php`
- Logged in as admin → `dashboard.php`
- Logged in as user → `dashboard.php` (configurable)

### Using the Modular Components

```php
// Initialize the authentication service
require_once 'src/AuthService.php';
$authService = new AuthService();

// Check if user is logged in
if ($authService->isLoggedIn()) {
    $user = $authService->getCurrentUser();
    echo "Welcome, " . htmlspecialchars($user['username']);
}

// Require login for protected pages
$authService->requireLogin();

// Login with rate limiting
$result = $authService->login($username, $password, $clientIp);
if ($result['success']) {
    // Login successful
} else {
    echo $result['error']; // Rate limiting or validation error
}

// Logout
$authService->logout();
```

## Rate Limiting Configuration

The system includes built-in rate limiting to prevent brute force attacks:

- **Default limits:** 5 login attempts per 5 minutes per IP address
- **Lockout duration:** Users see remaining lockout time in minutes
- **Storage:** Uses temporary files in system temp directory
- **Automatic cleanup:** Old attempts are automatically removed

To modify rate limiting settings, edit the parameters in `src/RateLimiter.php`:

```php
// Change max attempts (default: 5)
$rateLimiter = new RateLimiter($maxAttempts = 10, $timeWindow = 300);

// Change time window in seconds (default: 300 = 5 minutes)
$rateLimiter = new RateLimiter($maxAttempts = 5, $timeWindow = 600);
```

## Adding More Users

### Method 1: Direct SQL

Add more users by inserting records into the `users` table:

```sql
INSERT INTO users (username, password, role) 
VALUES ('newadmin', '$2y$10$hashed_password_here', 'admin');
```

### Method 2: Using AuthService

```php
$authService = new AuthService();

// Create admin user
$result = $authService->createUser('newadmin', 'secure_password', 'admin');

// Create regular user
$result = $authService->createUser('newuser', 'secure_password', 'user');

if ($result['success']) {
    echo "User created with ID: " . $result['user_id'];
} else {
    echo "Error: " . $result['error'];
}
```

### Password Hashing

Use PHP to hash passwords:

```php
$password = 'your_password';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo $hashed_password;
```

### Password Validation

The AuthService includes built-in password validation:
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter  
- At least one number

## Role-Based Access Control

The system supports two user roles:

### Admin Role
- Full access to admin dashboard
- Can manage users
- Can access all protected areas

### User Role
- Limited access (configurable)
- Can access user-specific areas
- Cannot access admin-only functions

### Using Role Checks

```php
// Require any logged-in user
$authService->requireLogin();

// Require admin role only
$authService->requireAdmin();

// Check user role
if ($authService->hasRole('admin')) {
    echo "Welcome admin!";
}

// Check if current user is admin
if ($authService->isAdmin()) {
    echo "Admin privileges granted";
}

// Get current user with role
$currentUser = $authService->getCurrentUser();
echo "Welcome " . $currentUser['username'] . " (" . $currentUser['role'] . ")";
```

## Customization

This is a modular boilerplate template. You can easily:

- Modify the dashboard content in `dashboard.php`
- Add more protected pages using `$authService->requireLogin()`
- Customize the styling in each file's `<style>` section
- Extend the authentication system by creating new service classes in `src/`
- Modify rate limiting by extending `src/RateLimiter.php`
- Add custom validation rules in `src/AuthService.php`
- Create new database methods in `src/Database.php`

## Requirements

- PHP 7.0 or higher
- MySQL 5.6 or higher
- Web server (Apache, Nginx, etc.)
