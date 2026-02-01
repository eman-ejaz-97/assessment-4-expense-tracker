<?php
/**
 * Smart Expense Tracking System - Forgot Password
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Handles password reset requests by sending a verification code via email
 */

$pageTitle = 'Forgot Password | Smart Expense Tracker';
$pageDescription = 'Reset your Smart Expense Tracker password by receiving a verification code via email.';
$pageKeywords = 'forgot password, reset password, password recovery';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard/');
}

$errors = [];
$success = false;
$email = '';

// Process forgot password form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        
        // Validate email
        if (empty($email)) {
            $errors[] = 'Email address is required.';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($errors)) {
            // Create password reset request
            $resetData = createPasswordResetRequest($email);
            
            if ($resetData) {
                // Send the reset code via email
                $emailSent = sendPasswordResetEmail($email, $resetData['first_name'], $resetData['code']);
                
                if ($emailSent) {
                    // Store email in session for the reset page
                    $_SESSION['reset_email'] = $email;
                    
                    logActivity('PASSWORD_RESET_REQUESTED', 'Password reset requested for: ' . $email, $resetData['user_id']);
                    
                    // Redirect to reset password page
                    setFlashMessage('success', 'A verification code has been sent to your email. Please check your inbox.');
                    redirect(SITE_URL . '/auth/reset-password.php');
                } else {
                    $errors[] = 'Failed to send reset email. Please try again later.';
                }
            } else {
                // Don't reveal if email exists or not (security best practice)
                // Still show success message to prevent user enumeration
                $_SESSION['reset_email'] = $email;
                setFlashMessage('success', 'If an account with that email exists, a verification code has been sent.');
                redirect(SITE_URL . '/auth/reset-password.php');
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
                        <h1 class="auth-card__title">Forgot Password?</h1>
                        <p class="auth-card__subtitle">Enter your email address and we'll send you a verification code to reset your password.</p>
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
                                   required autocomplete="email" placeholder="Enter your registered email">
                        </div>
                        
                        <button type="submit" class="btn btn--primary btn--lg" style="width: 100%;">
                            Send Reset Code
                        </button>
                    </form>
                    
                    <div class="auth-card__footer">
                        <p>Remember your password? <a href="<?php echo SITE_URL; ?>/auth/login.php">Back to login</a></p>
                        <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/auth/register.php">Sign up for free</a></p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
