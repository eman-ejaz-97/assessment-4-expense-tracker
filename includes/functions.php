<?php
/**
 * Smart Expense Tracking System - Common Functions
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * This file contains utility functions used throughout the application
 * for validation, sanitization, and common operations.
 */

require_once __DIR__ . '/../database/config.php';

// PHPMailer includes
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 * 
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * Must contain: 8+ chars, uppercase, lowercase, number, special char
 * 
 * @param string $password Password to validate
 * @return array [valid: bool, message: string]
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return [
        'valid' => empty($errors),
        'message' => implode('. ', $errors)
    ];
}

/**
 * Hash password securely using bcrypt
 * 
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 * 
 * @param string $password Plain text password
 * @param string $hash Stored hash
 * @return bool True if password matches
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token for form protection
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from form submission
 * 
 * @param string $token Token to verify
 * @return bool True if valid
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Display flash message
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message content
 * @return void
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message or null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message HTML
 * 
 * @return string HTML for flash message
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $typeClass = 'alert--' . $flash['type'];
        return '<div class="alert ' . $typeClass . '" role="alert">' . 
               htmlspecialchars($flash['message']) . 
               '<button type="button" class="alert__close" onclick="this.parentElement.remove()">&times;</button></div>';
    }
    return '';
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 * 
 * @param string|array $roles Role(s) to check
 * @return bool True if user has role
 */
function hasRole($roles) {
    if (!isLoggedIn()) return false;
    
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['user_role'] ?? '', $roles);
}

/**
 * Require user to be logged in
 * Redirects to login if not authenticated
 * 
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please log in to access this page.');
        redirect(SITE_URL . '/auth/login.php');
    }
}

/**
 * Require specific role(s) to access page
 * 
 * @param string|array $roles Required role(s)
 * @return void
 */
function requireRole($roles) {
    requireLogin();
    
    if (!hasRole($roles)) {
        setFlashMessage('error', 'You do not have permission to access this page.');
        redirect(SITE_URL . '/dashboard/index.php');
    }
}

/**
 * Get current user data
 * 
 * @return array|null User data or null
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Log activity for auditing
 * 
 * @param string $action Action performed
 * @param string $description Description of action
 * @param int|null $userId User ID (null for guest actions)
 * @return void
 */
function logActivity($action, $description = null, $userId = null) {
    $pdo = getDBConnection();
    
    if ($userId === null && isLoggedIn()) {
        $userId = $_SESSION['user_id'];
    }
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $description, $ip]);
}

/**
 * Send email using PHPMailer with Gmail SMTP
 * 
 * @param string $toEmail Recipient email address
 * @param string $toName Recipient name
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @param string $textBody Plain text email body (optional)
 * @return bool True if sent successfully
 */
function sendEmail($toEmail, $toName, $subject, $htmlBody, $textBody = '') {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(SMTP_FROM, SMTP_FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags($htmlBody);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send welcome/registration email to new user
 * 
 * @param string $email User's email address
 * @param string $firstName User's first name
 * @param string $username User's username
 * @return bool True if sent successfully
 */
function sendRegistrationEmail($email, $firstName, $username) {
    $subject = "Welcome to " . APP_NAME . "!";
    
    $htmlBody = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Welcome to ' . APP_NAME . '!</h1>
            </div>
            <div class="content">
                <h2>Hi ' . htmlspecialchars($firstName) . ',</h2>
                <p>Thank you for creating an account with us! Your registration was successful.</p>
                <p><strong>Your account details:</strong></p>
                <ul>
                    <li>Username: <strong>' . htmlspecialchars($username) . '</strong></li>
                    <li>Email: <strong>' . htmlspecialchars($email) . '</strong></li>
                </ul>
                <p>You can now log in and start tracking your expenses, setting budgets, and gaining insights into your spending habits.</p>
                <p style="text-align: center;">
                    <a href="' . SITE_URL . '/auth/login.php" class="button">Log In to Your Account</a>
                </p>
                <p>If you have any questions, feel free to contact us.</p>
                <p>Best regards,<br>The ' . APP_NAME . ' Team</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
                <p>This email was sent to ' . htmlspecialchars($email) . '</p>
            </div>
        </div>
    </body>
    </html>';
    
    $textBody = "Welcome to " . APP_NAME . "!\n\n" .
                "Hi " . $firstName . ",\n\n" .
                "Thank you for creating an account with us! Your registration was successful.\n\n" .
                "Your account details:\n" .
                "- Username: " . $username . "\n" .
                "- Email: " . $email . "\n\n" .
                "Log in at: " . SITE_URL . "/auth/login.php\n\n" .
                "Best regards,\nThe " . APP_NAME . " Team";
    
    return sendEmail($email, $firstName, $subject, $htmlBody, $textBody);
}

/**
 * Generate a 6-digit password reset code
 * 
 * @return string 6-digit code
 */
function generateResetCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Create a password reset request and store in database
 * 
 * @param string $email User's email address
 * @return array|false [code, token] on success, false on failure
 */
function createPasswordResetRequest($email) {
    $pdo = getDBConnection();
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT user_id, first_name FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return false;
    }
    
    // Invalidate any existing reset requests for this user
    $stmt = $pdo->prepare("UPDATE password_resets SET used = TRUE WHERE user_id = ? AND used = FALSE");
    $stmt->execute([$user['user_id']]);
    
    // Generate new code and token
    $code = generateResetCode();
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    
    // Store in database - use PostgreSQL's NOW() + interval for consistent timezone
    $stmt = $pdo->prepare("
        INSERT INTO password_resets (user_id, email, reset_code, token_hash, expires_at)
        VALUES (?, ?, ?, ?, NOW() + INTERVAL '15 minutes')
    ");
    $stmt->execute([$user['user_id'], $email, $code, $tokenHash]);
    
    return [
        'code' => $code,
        'token' => $token,
        'first_name' => $user['first_name'],
        'user_id' => $user['user_id']
    ];
}

/**
 * Verify a password reset code
 * 
 * @param string $email User's email
 * @param string $code 6-digit reset code
 * @return array|false User data on success, false on failure
 */
function verifyResetCode($email, $code) {
    $pdo = getDBConnection();
    
    // Debug: First check if any record exists for this email
    $debugStmt = $pdo->prepare("
        SELECT pr.reset_id, pr.reset_code, pr.email, pr.used, pr.expires_at, NOW() as current_time,
               (pr.expires_at > NOW()) as is_valid_time
        FROM password_resets pr
        WHERE LOWER(pr.email) = LOWER(?)
        ORDER BY pr.created_at DESC
        LIMIT 1
    ");
    $debugStmt->execute([$email]);
    $debugData = $debugStmt->fetch();
    
    // Log debug info
    if ($debugData) {
        error_log("Password Reset Debug: Found record for email: $email");
        error_log("Password Reset Debug: Stored code: " . $debugData['reset_code'] . ", Submitted code: $code");
        error_log("Password Reset Debug: Used: " . ($debugData['used'] ? 'true' : 'false'));
        error_log("Password Reset Debug: Expires at: " . $debugData['expires_at'] . ", Current time: " . $debugData['current_time']);
        error_log("Password Reset Debug: Is valid time: " . ($debugData['is_valid_time'] ? 'true' : 'false'));
        error_log("Password Reset Debug: Code match: " . ($debugData['reset_code'] === $code ? 'true' : 'false'));
    } else {
        error_log("Password Reset Debug: No record found for email: $email");
    }
    
    // Use LOWER() for case-insensitive email comparison
    $stmt = $pdo->prepare("
        SELECT pr.reset_id, pr.user_id, pr.email, u.first_name
        FROM password_resets pr
        JOIN users u ON pr.user_id = u.user_id
        WHERE LOWER(pr.email) = LOWER(?) 
          AND pr.reset_code = ? 
          AND pr.used = FALSE 
          AND pr.expires_at > NOW()
        ORDER BY pr.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$email, $code]);
    
    return $stmt->fetch();
}

/**
 * Complete the password reset process
 * 
 * @param string $email User's email
 * @param string $code Reset code
 * @param string $newPassword New password
 * @return bool True on success
 */
function resetPassword($email, $code, $newPassword) {
    $pdo = getDBConnection();
    
    // Verify the code first
    $resetData = verifyResetCode($email, $code);
    if (!$resetData) {
        return false;
    }
    
    // Update the password
    $hashedPassword = hashPassword($newPassword);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $stmt->execute([$hashedPassword, $resetData['user_id']]);
    
    // Mark the reset code as used
    $stmt = $pdo->prepare("UPDATE password_resets SET used = TRUE WHERE reset_id = ?");
    $stmt->execute([$resetData['reset_id']]);
    
    // Log the activity
    logActivity('PASSWORD_RESET', 'Password reset completed for: ' . $email, $resetData['user_id']);
    
    return true;
}

/**
 * Send password reset email with verification code
 * 
 * @param string $email User's email address
 * @param string $firstName User's first name
 * @param string $code 6-digit verification code
 * @return bool True if sent successfully
 */
function sendPasswordResetEmail($email, $firstName, $code) {
    $subject = "Password Reset Code - " . APP_NAME;
    
    $htmlBody = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .code-box { background: #fff; border: 2px dashed #e74c3c; padding: 20px; text-align: center; margin: 20px 0; border-radius: 10px; }
            .code { font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #e74c3c; font-family: monospace; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px 15px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Password Reset Request</h1>
            </div>
            <div class="content">
                <h2>Hi ' . htmlspecialchars($firstName) . ',</h2>
                <p>We received a request to reset your password for your ' . APP_NAME . ' account.</p>
                <p>Please use the following verification code to reset your password:</p>
                <div class="code-box">
                    <div class="code">' . $code . '</div>
                </div>
                <div class="warning">
                    <strong>Important:</strong> This code will expire in <strong>15 minutes</strong>. 
                    If you didn\'t request this password reset, please ignore this email.
                </div>
                <p>Enter this code on the password reset page to create a new password.</p>
                <p>Best regards,<br>The ' . APP_NAME . ' Team</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
                <p>This email was sent to ' . htmlspecialchars($email) . '</p>
            </div>
        </div>
    </body>
    </html>';
    
    $textBody = "Password Reset Request\n\n" .
                "Hi " . $firstName . ",\n\n" .
                "We received a request to reset your password.\n\n" .
                "Your verification code is: " . $code . "\n\n" .
                "This code will expire in 15 minutes.\n\n" .
                "If you didn't request this, please ignore this email.\n\n" .
                "Best regards,\nThe " . APP_NAME . " Team";
    
    return sendEmail($email, $firstName, $subject, $htmlBody, $textBody);
}

/**
 * Send password change confirmation email
 * 
 * @param string $email User's email address
 * @param string $firstName User's first name
 * @return bool True if sent successfully
 */
function sendPasswordChangedEmail($email, $firstName) {
    $subject = "Password Changed Successfully - " . APP_NAME;
    
    $htmlBody = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .success-icon { font-size: 48px; margin-bottom: 10px; }
            .warning { background: #f8d7da; border-left: 4px solid #dc3545; padding: 10px 15px; margin: 20px 0; }
            .button { display: inline-block; background: #27ae60; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="success-icon">✓</div>
                <h1>Password Changed</h1>
            </div>
            <div class="content">
                <h2>Hi ' . htmlspecialchars($firstName) . ',</h2>
                <p>Your password has been successfully changed.</p>
                <p>You can now log in to your account using your new password.</p>
                <p style="text-align: center;">
                    <a href="' . SITE_URL . '/auth/login.php" class="button">Log In Now</a>
                </p>
                <div class="warning">
                    <strong>Didn\'t make this change?</strong><br>
                    If you did not change your password, please contact us immediately as your account may be compromised.
                </div>
                <p>Best regards,<br>The ' . APP_NAME . ' Team</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
                <p>This email was sent to ' . htmlspecialchars($email) . '</p>
            </div>
        </div>
    </body>
    </html>';
    
    $textBody = "Password Changed Successfully\n\n" .
                "Hi " . $firstName . ",\n\n" .
                "Your password has been successfully changed.\n\n" .
                "You can now log in at: " . SITE_URL . "/auth/login.php\n\n" .
                "If you didn't make this change, please contact us immediately.\n\n" .
                "Best regards,\nThe " . APP_NAME . " Team";
    
    return sendEmail($email, $firstName, $subject, $htmlBody, $textBody);
}

/**
 * Create a password change verification request for logged-in users
 * 
 * @param int $userId User ID
 * @param string $email User's email
 * @return array|false [code, first_name] on success, false on failure
 */
function createPasswordChangeRequest($userId, $email) {
    $pdo = getDBConnection();
    
    // Get user info
    $stmt = $pdo->prepare("SELECT first_name FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return false;
    }
    
    // Invalidate any existing requests
    $stmt = $pdo->prepare("UPDATE password_resets SET used = TRUE WHERE user_id = ? AND used = FALSE");
    $stmt->execute([$userId]);
    
    // Generate new code
    $code = generateResetCode();
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    
    // Store in database - use PostgreSQL's NOW() + interval for consistent timezone
    $stmt = $pdo->prepare("
        INSERT INTO password_resets (user_id, email, reset_code, token_hash, expires_at)
        VALUES (?, ?, ?, ?, NOW() + INTERVAL '15 minutes')
    ");
    $stmt->execute([$userId, $email, $code, $tokenHash]);
    
    return [
        'code' => $code,
        'first_name' => $user['first_name']
    ];
}

/**
 * Send password change verification email for logged-in users
 * 
 * @param string $email User's email address
 * @param string $firstName User's first name
 * @param string $code 6-digit verification code
 * @return bool True if sent successfully
 */
function sendPasswordChangeVerificationEmail($email, $firstName, $code) {
    $subject = "Password Change Verification - " . APP_NAME;
    
    $htmlBody = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .code-box { background: #fff; border: 2px dashed #667eea; padding: 20px; text-align: center; margin: 20px 0; border-radius: 10px; }
            .code { font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #667eea; font-family: monospace; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px 15px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Password Change Request</h1>
            </div>
            <div class="content">
                <h2>Hi ' . htmlspecialchars($firstName) . ',</h2>
                <p>You have requested to change your password. Please use the following verification code to confirm this action:</p>
                <div class="code-box">
                    <div class="code">' . $code . '</div>
                </div>
                <div class="warning">
                    <strong>Important:</strong> This code will expire in <strong>15 minutes</strong>. 
                    If you didn\'t request this change, please secure your account immediately.
                </div>
                <p>Enter this code on the password change page to set your new password.</p>
                <p>Best regards,<br>The ' . APP_NAME . ' Team</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
                <p>This email was sent to ' . htmlspecialchars($email) . '</p>
            </div>
        </div>
    </body>
    </html>';
    
    $textBody = "Password Change Verification\n\n" .
                "Hi " . $firstName . ",\n\n" .
                "You have requested to change your password.\n\n" .
                "Your verification code is: " . $code . "\n\n" .
                "This code will expire in 15 minutes.\n\n" .
                "If you didn't request this, please secure your account.\n\n" .
                "Best regards,\nThe " . APP_NAME . " Team";
    
    return sendEmail($email, $firstName, $subject, $htmlBody, $textBody);
}

/**
 * Format currency amount
 * 
 * @param float $amount Amount to format
 * @param string $currency Currency code
 * @return string Formatted amount
 */
function formatCurrency($amount, $currency = 'AUD') {
    $symbols = [
        'AUD' => '$',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£'
    ];
    
    $symbol = $symbols[$currency] ?? '$';
    return $symbol . number_format($amount, 2);
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

/**
 * Get user's expenses with optional filters
 * 
 * @param int $userId User ID
 * @param array $filters Optional filters
 * @return array Expenses
 */
function getUserExpenses($userId, $filters = []) {
    $pdo = getDBConnection();
    
    $sql = "SELECT e.*, c.category_name, c.category_icon, c.category_color 
            FROM expenses e 
            JOIN expense_categories c ON e.category_id = c.category_id 
            WHERE e.user_id = ?";
    $params = [$userId];
    
    // Apply date filter
    if (!empty($filters['start_date'])) {
        $sql .= " AND e.expense_date >= ?";
        $params[] = $filters['start_date'];
    }
    if (!empty($filters['end_date'])) {
        $sql .= " AND e.expense_date <= ?";
        $params[] = $filters['end_date'];
    }
    
    // Apply category filter
    if (!empty($filters['category_id'])) {
        $sql .= " AND e.category_id = ?";
        $params[] = $filters['category_id'];
    }
    
    $sql .= " ORDER BY e.expense_date DESC, e.created_at DESC";
    
    // Apply limit
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT " . intval($filters['limit']);
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get expense categories (default + user custom)
 * 
 * @param int|null $userId User ID for custom categories
 * @return array Categories
 */
function getCategories($userId = null) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM expense_categories WHERE is_default = TRUE";
    $params = [];
    
    if ($userId) {
        $sql .= " OR user_id = ?";
        $params[] = $userId;
    }
    
    $sql .= " ORDER BY is_default DESC, category_name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get spending summary for dashboard
 * 
 * @param int $userId User ID
 * @param int $month Month (1-12)
 * @param int $year Year
 * @return array Summary data
 */
function getSpendingSummary($userId, $month = null, $year = null) {
    $pdo = getDBConnection();
    
    if ($month === null) $month = date('n');
    if ($year === null) $year = date('Y');
    
    // Total spending this month
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM expenses 
        WHERE user_id = ? AND EXTRACT(MONTH FROM expense_date) = ? AND EXTRACT(YEAR FROM expense_date) = ?
    ");
    $stmt->execute([$userId, $month, $year]);
    $totalSpending = $stmt->fetch()['total'];
    
    // Spending by category
    $stmt = $pdo->prepare("
        SELECT c.category_name, c.category_icon, c.category_color, 
               COALESCE(SUM(e.amount), 0) as total
        FROM expense_categories c
        LEFT JOIN expenses e ON c.category_id = e.category_id 
            AND e.user_id = ? AND EXTRACT(MONTH FROM e.expense_date) = ? AND EXTRACT(YEAR FROM e.expense_date) = ?
        WHERE c.is_default = TRUE OR c.user_id = ?
        GROUP BY c.category_id, c.category_name, c.category_icon, c.category_color
        ORDER BY total DESC
    ");
    $stmt->execute([$userId, $month, $year, $userId]);
    $byCategory = $stmt->fetchAll();
    
    // Recent transactions
    $recentExpenses = getUserExpenses($userId, ['limit' => 5]);
    
    return [
        'total_spending' => $totalSpending,
        'by_category' => $byCategory,
        'recent_expenses' => $recentExpenses,
        'month' => $month,
        'year' => $year
    ];
}

/**
 * Validate phone number format
 * 
 * @param string $phone Phone number
 * @return bool True if valid
 */
function isValidPhone($phone) {
    // Allow various phone formats
    $pattern = '/^[\d\s\-\+\(\)]+$/';
    return preg_match($pattern, $phone) && strlen(preg_replace('/\D/', '', $phone)) >= 8;
}

/**
 * Generate pagination HTML
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $baseUrl Base URL for pagination links
 * @return string Pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) return '';
    
    $html = '<nav class="pagination" aria-label="Pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="pagination__link">&laquo; Previous</a>';
    }
    
    // Page numbers
    for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
        $activeClass = ($i === $currentPage) ? 'pagination__link--active' : '';
        $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination__link ' . $activeClass . '">' . $i . '</a>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="pagination__link">Next &raquo;</a>';
    }
    
    $html .= '</nav>';
    return $html;
}
?>
