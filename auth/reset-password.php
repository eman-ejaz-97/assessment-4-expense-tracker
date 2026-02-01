<?php
/**
 * Smart Expense Tracking System - Reset Password
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Handles password reset using verification code sent via email
 */

$pageTitle = 'Reset Password | Smart Expense Tracker';
$pageDescription = 'Enter your verification code and create a new password for your Smart Expense Tracker account.';
$pageKeywords = 'reset password, verification code, new password';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard/');
}

$errors = [];
$email = $_SESSION['reset_email'] ?? '';
$code = '';

// Process reset password form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $code = trim($_POST['code'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate email
        if (empty($email)) {
            $errors[] = 'Email address is required.';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        // Validate code
        if (empty($code)) {
            $errors[] = 'Verification code is required.';
        } elseif (!preg_match('/^\d{6}$/', $code)) {
            $errors[] = 'Please enter a valid 6-digit code.';
        }
        
        // Validate password
        if (empty($password)) {
            $errors[] = 'New password is required.';
        } else {
            $passwordValidation = validatePassword($password);
            if (!$passwordValidation['valid']) {
                $errors[] = $passwordValidation['message'];
            }
        }
        
        // Confirm password match
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        
        if (empty($errors)) {
            // Verify the reset code
            $resetData = verifyResetCode($email, $code);
            
            if ($resetData) {
                // Reset the password
                if (resetPassword($email, $code, $password)) {
                    // Send confirmation email
                    sendPasswordChangedEmail($email, $resetData['first_name']);
                    
                    // Clear the reset email from session
                    unset($_SESSION['reset_email']);
                    
                    setFlashMessage('success', 'Your password has been reset successfully! Please log in with your new password.');
                    redirect(SITE_URL . '/auth/login.php');
                } else {
                    $errors[] = 'Failed to reset password. Please try again.';
                }
            } else {
                $errors[] = 'Invalid or expired verification code. Please request a new one.';
            }
        }
    }
}

// Generate CSRF token
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords); ?>">
    <meta name="author" content="Smart Expense Tracking System - Student ID: 20034038">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/styles.css">
    <style>
        .code-input {
            font-size: 1.5rem;
            letter-spacing: 0.5rem;
            text-align: center;
            font-family: monospace;
        }
        .resend-link {
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <header class="header" role="banner">
        <div class="header__inner">
            <a href="<?php echo SITE_URL; ?>/index.php" class="logo" aria-label="Smart Expense Tracker Home">
                <span class="logo__icon" aria-hidden="true">$</span>
                <span>ExpenseTracker</span>
            </a>
        </div>
    </header>
    
    <main id="main-content">
        <section class="auth-section">
            <div class="container">
                <div class="auth-card">
                    <div class="auth-card__header">
                        <h1 class="auth-card__title">Reset Your Password</h1>
                        <p class="auth-card__subtitle">Enter the 6-digit code sent to your email and create a new password.</p>
                    </div>
                    
                    <?php echo displayFlashMessage(); ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert--error" role="alert">
                            <ul style="margin: 0; padding-left: 1.5rem;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="auth-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                Email Address <span class="required" aria-hidden="true">*</span>
                            </label>
                            <input type="email" id="email" name="email" class="form-input"
                                   value="<?php echo htmlspecialchars($email); ?>"
                                   required autocomplete="email" placeholder="Enter your email">
                        </div>
                        
                        <div class="form-group">
                            <label for="code" class="form-label">
                                Verification Code <span class="required" aria-hidden="true">*</span>
                            </label>
                            <input type="text" id="code" name="code" class="form-input code-input"
                                   value="<?php echo htmlspecialchars($code); ?>"
                                   required maxlength="6" pattern="\d{6}" 
                                   placeholder="000000" autocomplete="one-time-code">
                            <small class="form-hint">Enter the 6-digit code from your email</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">
                                New Password <span class="required" aria-hidden="true">*</span>
                            </label>
                            <input type="password" id="password" name="password" class="form-input"
                                   required minlength="8" autocomplete="new-password"
                                   placeholder="Create a strong password">
                            <small class="form-hint">
                                Must be 8+ characters with uppercase, lowercase, number, and special character
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                Confirm New Password <span class="required" aria-hidden="true">*</span>
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                                   required autocomplete="new-password" placeholder="Confirm your new password">
                        </div>
                        
                        <button type="submit" class="btn btn--primary btn--lg" style="width: 100%;">
                            Reset Password
                        </button>
                    </form>
                    
                    <div class="resend-link">
                        <p>Didn't receive the code? <a href="<?php echo SITE_URL; ?>/auth/forgot-password.php">Request a new code</a></p>
                    </div>
                    
                    <div class="auth-card__footer">
                        <p>Remember your password? <a href="<?php echo SITE_URL; ?>/auth/login.php">Back to login</a></p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
    <script>
        // Auto-format code input (numbers only)
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 6);
        });
    </script>
</body>
</html>
