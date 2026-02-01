<?php
/**
 * Smart Expense Tracking System - User Registration
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Handles new user registration with validation and secure password hashing
 * Implements CSRF protection and input validation
 */

$pageTitle = 'Create Account | Smart Expense Tracker';
$pageDescription = 'Register for a free Smart Expense Tracker account and start managing your finances today.';
$pageKeywords = 'register, sign up, create account, expense tracker registration';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard/');
}

$errors = [];
$formData = [
    'first_name' => '',
    'last_name' => '',
    'username' => '',
    'email' => '',
    'phone' => ''
];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Sanitize and collect form data
        $formData['first_name'] = sanitizeInput($_POST['first_name'] ?? '');
        $formData['last_name'] = sanitizeInput($_POST['last_name'] ?? '');
        $formData['username'] = sanitizeInput($_POST['username'] ?? '');
        $formData['email'] = strtolower(trim($_POST['email'] ?? ''));
        $formData['phone'] = sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate first name
        if (empty($formData['first_name'])) {
            $errors[] = 'First name is required.';
        } elseif (strlen($formData['first_name']) < 2) {
            $errors[] = 'First name must be at least 2 characters.';
        }
        
        // Validate last name
        if (empty($formData['last_name'])) {
            $errors[] = 'Last name is required.';
        } elseif (strlen($formData['last_name']) < 2) {
            $errors[] = 'Last name must be at least 2 characters.';
        }
        
        // Validate username
        if (empty($formData['username'])) {
            $errors[] = 'Username is required.';
        } elseif (strlen($formData['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $formData['username'])) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }
        
        // Validate email
        if (empty($formData['email'])) {
            $errors[] = 'Email address is required.';
        } elseif (!isValidEmail($formData['email'])) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        // Validate phone (optional)
        if (!empty($formData['phone']) && !isValidPhone($formData['phone'])) {
            $errors[] = 'Please enter a valid phone number.';
        }
        
        // Validate password
        if (empty($password)) {
            $errors[] = 'Password is required.';
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
        
        // Check for existing username/email
        if (empty($errors)) {
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$formData['username'], $formData['email']]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Username or email already exists. Please choose a different one.';
            }
        }
        
        // Create user account
        if (empty($errors)) {
            $pdo = getDBConnection();
            
            $hashedPassword = hashPassword($password);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, first_name, last_name, phone, role, status)
                VALUES (?, ?, ?, ?, ?, ?, 'user', 'active')
            ");
            
            try {
                $stmt->execute([
                    $formData['username'],
                    $formData['email'],
                    $hashedPassword,
                    $formData['first_name'],
                    $formData['last_name'],
                    $formData['phone'] ?: null
                ]);
                
                logActivity('REGISTRATION', 'New user registered: ' . $formData['username']);
                
                // Send welcome email
                $emailSent = sendRegistrationEmail(
                    $formData['email'],
                    $formData['first_name'],
                    $formData['username']
                );
                
                if ($emailSent) {
                    setFlashMessage('success', 'Account created successfully! A confirmation email has been sent. Please log in to continue.');
                } else {
                    setFlashMessage('success', 'Account created successfully! Please log in to continue.');
                }
                redirect(SITE_URL . '/auth/login.php');
                
            } catch (PDOException $e) {
                error_log("Registration Error: " . $e->getMessage());
                $errors[] = 'An error occurred during registration. Please try again.';
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
                        <h1 class="auth-card__title">Create Your Account</h1>
                        <p class="auth-card__subtitle">Join thousands of users managing their finances smarter</p>
                    </div>
                    
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
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name" class="form-label">
                                    First Name <span class="required" aria-hidden="true">*</span>
                                </label>
                                <input type="text" id="first_name" name="first_name" class="form-input" 
                                       value="<?php echo htmlspecialchars($formData['first_name']); ?>"
                                       required minlength="2" maxlength="50" autocomplete="given-name"
                                       placeholder="Enter your first name">
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label">
                                    Last Name <span class="required" aria-hidden="true">*</span>
                                </label>
                                <input type="text" id="last_name" name="last_name" class="form-input"
                                       value="<?php echo htmlspecialchars($formData['last_name']); ?>"
                                       required minlength="2" maxlength="50" autocomplete="family-name"
                                       placeholder="Enter your last name">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="username" class="form-label">
                                Username <span class="required" aria-hidden="true">*</span>
                            </label>
                            <input type="text" id="username" name="username" class="form-input"
                                   value="<?php echo htmlspecialchars($formData['username']); ?>"
                                   required minlength="3" maxlength="50" autocomplete="username"
                                   pattern="[a-zA-Z0-9_]+" placeholder="Choose a username">
                            <small class="form-hint">Letters, numbers, and underscores only</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                Email Address <span class="required" aria-hidden="true">*</span>
                            </label>
                            <input type="email" id="email" name="email" class="form-input"
                                   value="<?php echo htmlspecialchars($formData['email']); ?>"
                                   required autocomplete="email" placeholder="Enter your email">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-input"
                                   value="<?php echo htmlspecialchars($formData['phone']); ?>"
                                   autocomplete="tel" placeholder="Enter your phone (optional)">
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">
                                Password <span class="required" aria-hidden="true">*</span>
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
                                Confirm Password <span class="required" aria-hidden="true">*</span>
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                                   required autocomplete="new-password" placeholder="Confirm your password">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="terms" required>
                                <span>I agree to the <a href="<?php echo SITE_URL; ?>/terms.php" target="_blank">Terms of Service</a> 
                                and <a href="<?php echo SITE_URL; ?>/privacy-policy.php" target="_blank">Privacy Policy</a></span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn--primary btn--lg" style="width: 100%;">
                            Create Account
                        </button>
                    </form>
                    
                    <div class="auth-card__footer">
                        <p>Already have an account? <a href="<?php echo SITE_URL; ?>/auth/login.php">Log in here</a></p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
