<?php
/**
 * Smart Expense Tracking System - Session Management
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * This file handles secure session configuration and management
 * Implements security best practices for session handling
 */

// Prevent session fixation attacks
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);

// Configure secure session cookie parameters
$sessionParams = [
    'lifetime' => 3600, // 1 hour
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), // Only send over HTTPS in production
    'httponly' => true, // Prevent JavaScript access
    'samesite' => 'Lax' // CSRF protection
];

session_set_cookie_params($sessionParams);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID periodically to prevent session fixation
if (!isset($_SESSION['regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = time();
} elseif (time() - $_SESSION['regenerated'] > 1800) { // Regenerate every 30 minutes
    session_regenerate_id(true);
    $_SESSION['regenerated'] = time();
}

// Check for session timeout (1 hour of inactivity)
$sessionTimeout = 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionTimeout)) {
    // Session expired - destroy and redirect
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['flash_message'] = [
        'type' => 'warning',
        'message' => 'Your session has expired. Please log in again.'
    ];
}
$_SESSION['last_activity'] = time();

/**
 * Create user session after successful login
 * 
 * @param array $user User data from database
 * @return void
 */
function createUserSession($user) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['regenerated'] = time();
    
    // Update last login timestamp
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);
    
    // Log the login activity
    logActivity('LOGIN', 'User logged in successfully', $user['user_id']);
}

/**
 * Destroy user session (logout)
 * 
 * @return void
 */
function destroyUserSession() {
    if (isLoggedIn()) {
        logActivity('LOGOUT', 'User logged out');
    }
    
    // Unset all session variables
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Check if account is locked due to too many failed attempts
 * 
 * @param array $user User data
 * @return bool True if account is locked
 */
function isAccountLocked($user) {
    if ($user['locked_until'] !== null) {
        if (strtotime($user['locked_until']) > time()) {
            return true;
        }
    }
    return false;
}

/**
 * Record failed login attempt
 * 
 * @param int $userId User ID
 * @return void
 */
function recordFailedLogin($userId) {
    $pdo = getDBConnection();
    
    // Get current attempts
    $stmt = $pdo->prepare("SELECT login_attempts FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $attempts = $stmt->fetch()['login_attempts'] + 1;
    
    // Lock account after 5 failed attempts
    $lockUntil = null;
    if ($attempts >= 5) {
        $lockUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    }
    
    $stmt = $pdo->prepare("UPDATE users SET login_attempts = ?, locked_until = ? WHERE user_id = ?");
    $stmt->execute([$attempts, $lockUntil, $userId]);
    
    logActivity('FAILED_LOGIN', "Failed login attempt ($attempts/5)", $userId);
}

/**
 * Get remaining lockout time in minutes
 * 
 * @param string $lockedUntil Lock expiry datetime
 * @return int Minutes remaining
 */
function getRemainingLockTime($lockedUntil) {
    $remaining = strtotime($lockedUntil) - time();
    return max(0, ceil($remaining / 60));
}
?>
