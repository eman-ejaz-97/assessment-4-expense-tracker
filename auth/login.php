<?php
/**
 * Smart Expense Tracking System - User Login
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Handles user authentication with secure password verification
 * Implements CSRF protection, rate limiting, and account lockout
 */

$pageTitle = 'Login | Smart Expense Tracker';
$pageDescription = 'Log in to your Smart Expense Tracker account to manage your expenses and budgets.';
$pageKeywords = 'login, sign in, expense tracker login, account access';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard/');
}

$errors = [];
$email = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        // Validate input
        if (empty($email)) {
            $errors[] = 'Email address is required.';
        }
        if (empty($password)) {
            $errors[] = 'Password is required.';
        }
        
        // Attempt login
        if (empty($errors)) {
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("
                SELECT user_id, username, email, password_hash, first_name, last_name, 
                       role, status, login_attempts, locked_until
                FROM users 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                // User not found - use generic message to prevent user enumeration
                $errors[] = 'Invalid email or password.';
                logActivity('FAILED_LOGIN', "Login attempt with unknown email: $email");
            } elseif ($user['status'] !== 'active') {
                $errors[] = 'Your account has been suspended. Please contact support.';
            } elseif (isAccountLocked($user)) {
                $remainingMinutes = getRemainingLockTime($user['locked_until']);
                $errors[] = "Account temporarily locked. Please try again in $remainingMinutes minutes.";
            } elseif (!verifyPassword($password, $user['password_hash'])) {
                // Wrong password
                recordFailedLogin($user['user_id']);
                $attempts = $user['login_attempts'] + 1;
                
                if ($attempts >= 5) {
                    $errors[] = 'Account locked due to too many failed attempts. Please try again in 30 minutes.';
                } else {
                    $remaining = 5 - $attempts;
                    $errors[] = "Invalid email or password. $remaining attempt(s) remaining.";
                }
            } else {
                // Successful login
                createUserSession($user);
                
                // Set remember me cookie if requested
                if ($rememberMe) {
                    $token = bin2hex(random_bytes(32));
                    // Store token in database and set cookie (simplified for demo)
                    setcookie('remember_token', $token, time() + (86400 * 30), '/');
                }
                
                setFlashMessage('success', 'Welcome back, ' . htmlspecialchars($user['first_name']) . '!');
                
                // Redirect based on role
                if (hasRole('admin')) {
                    redirect(SITE_URL . '/admin/');
                } else {
                    redirect(SITE_URL . '/dashboard/');
                }
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
                        <h1 class="auth-card__title">Welcome Back</h1>
                        <p class="auth-card__subtitle">Log in to your account to continue</p>
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
                            <label for="password" class="form-label">
                                Password <span class="required" aria-hidden="true">*</span>
                            </label>
                            <input type="password" id="password" name="password" class="form-input"
                                   required autocomplete="current-password" placeholder="Enter your password">
                        </div>
                        
                        <div class="form-group form-group--flex">
                            <label class="form-checkbox">
                                <input type="checkbox" name="remember_me">
                                <span>Remember me</span>
                            </label>
                            <a href="<?php echo SITE_URL; ?>/auth/forgot-password.php" class="form-link">Forgot password?</a>
                        </div>
                        
                        <button type="submit" class="btn btn--primary btn--lg" style="width: 100%;">
                            Log In
                        </button>
                    </form>
                    
                    <div class="auth-card__divider">
                        <span>Demo Accounts</span>
                    </div>
                    
                    <div class="demo-accounts">
                        <div class="demo-account">
                            <strong>Admin:</strong> admin@expensetracker.com
                        </div>
                        <div class="demo-account">
                            <strong>Member:</strong> member@expensetracker.com
                        </div>
                        <div class="demo-account">
                            <strong>User:</strong> user@expensetracker.com
                        </div>
                        <div class="demo-account">
                            <em>Password for all: password</em>
                        </div>
                    </div>
                    
                    <div class="auth-card__footer">
                        <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/auth/register.php">Sign up for free</a></p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
