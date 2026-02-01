<?php
/**
 * Smart Expense Tracking System - User Profile
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * User profile management page
 */

$pageTitle = 'My Profile | Smart Expense Tracker';
$pageDescription = 'Manage your account settings and profile information.';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Require authentication
requireLogin();

$userId = $_SESSION['user_id'];
$user = getCurrentUser();

$errors = [];
$success = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $firstName = sanitizeInput($_POST['first_name'] ?? '');
            $lastName = sanitizeInput($_POST['last_name'] ?? '');
            $phone = sanitizeInput($_POST['phone'] ?? '');
            
            // Validation
            if (empty($firstName) || strlen($firstName) < 2) {
                $errors[] = 'First name must be at least 2 characters.';
            }
            if (empty($lastName) || strlen($lastName) < 2) {
                $errors[] = 'Last name must be at least 2 characters.';
            }
            if (!empty($phone) && !isValidPhone($phone)) {
                $errors[] = 'Please enter a valid phone number.';
            }
            
            if (empty($errors)) {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?, phone = ?, updated_at = NOW()
                    WHERE user_id = ?
                ");
                $stmt->execute([$firstName, $lastName, $phone ?: null, $userId]);
                
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                
                logActivity('PROFILE_UPDATE', 'Updated profile information');
                setFlashMessage('success', 'Profile updated successfully!');
                redirect(SITE_URL . '/dashboard/profile.php');
            }
        } elseif ($action === 'request_password_change') {
            // Step 1: Verify current password and send verification code
            $currentPassword = $_POST['current_password'] ?? '';
            
            if (empty($currentPassword)) {
                $errors[] = 'Current password is required.';
            } elseif (!verifyPassword($currentPassword, $user['password_hash'])) {
                $errors[] = 'Current password is incorrect.';
            }
            
            if (empty($errors)) {
                // Generate and send verification code
                $requestData = createPasswordChangeRequest($userId, $user['email']);
                
                if ($requestData) {
                    $emailSent = sendPasswordChangeVerificationEmail(
                        $user['email'], 
                        $requestData['first_name'], 
                        $requestData['code']
                    );
                    
                    if ($emailSent) {
                        $_SESSION['password_change_pending'] = true;
                        $_SESSION['password_change_email'] = $user['email'];
                        logActivity('PASSWORD_CHANGE_REQUESTED', 'Password change verification code sent');
                        setFlashMessage('success', 'A verification code has been sent to your email. Please enter it below.');
                    } else {
                        $errors[] = 'Failed to send verification email. Please try again.';
                    }
                } else {
                    $errors[] = 'Failed to initiate password change. Please try again.';
                }
            }
            
        } elseif ($action === 'verify_and_change_password') {
            // Step 2: Verify code and change password
            $code = trim($_POST['verification_code'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validate code
            if (empty($code)) {
                $errors[] = 'Verification code is required.';
            } elseif (!preg_match('/^\d{6}$/', $code)) {
                $errors[] = 'Please enter a valid 6-digit code.';
            }
            
            // Validate new password
            if (empty($newPassword)) {
                $errors[] = 'New password is required.';
            } else {
                $passwordValidation = validatePassword($newPassword);
                if (!$passwordValidation['valid']) {
                    $errors[] = $passwordValidation['message'];
                }
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'New passwords do not match.';
            }
            
            if (empty($errors)) {
                // Debug: Check what's in the database
                $pdo = getDBConnection();
                $debugStmt = $pdo->prepare("
                    SELECT reset_code, used, expires_at, NOW() as db_now
                    FROM password_resets 
                    WHERE LOWER(email) = LOWER(?)
                    ORDER BY created_at DESC LIMIT 1
                ");
                $debugStmt->execute([$user['email']]);
                $debugInfo = $debugStmt->fetch();
                
                if ($debugInfo) {
                    // Temporarily show debug info
                    $errors[] = "DEBUG - Stored code: " . $debugInfo['reset_code'] . " | You entered: " . $code;
                    $errors[] = "DEBUG - Used: " . ($debugInfo['used'] ? 'YES' : 'NO') . " | Expires: " . $debugInfo['expires_at'] . " | DB Now: " . $debugInfo['db_now'];
                } else {
                    $errors[] = "DEBUG - No reset record found for email: " . $user['email'];
                }
                
                // Verify the code
                $resetData = verifyResetCode($user['email'], $code);
                
                if ($resetData) {
                    // Update password
                    $pdo = getDBConnection();
                    $hashedPassword = hashPassword($newPassword);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?");
                    $stmt->execute([$hashedPassword, $userId]);
                    
                    // Mark code as used
                    $stmt = $pdo->prepare("UPDATE password_resets SET used = TRUE WHERE reset_id = ?");
                    $stmt->execute([$resetData['reset_id']]);
                    
                    // Clear session flags
                    unset($_SESSION['password_change_pending']);
                    unset($_SESSION['password_change_email']);
                    
                    // Send confirmation email
                    sendPasswordChangedEmail($user['email'], $user['first_name']);
                    
                    logActivity('PASSWORD_CHANGE', 'Password changed with email verification');
                    setFlashMessage('success', 'Password changed successfully! A confirmation email has been sent.');
                    redirect(SITE_URL . '/dashboard/profile.php');
                } else {
                    $errors[] = 'Invalid or expired verification code. Please request a new one.';
                }
            }
            
        } elseif ($action === 'cancel_password_change') {
            // Cancel the password change process
            unset($_SESSION['password_change_pending']);
            unset($_SESSION['password_change_email']);
            setFlashMessage('info', 'Password change cancelled.');
            redirect(SITE_URL . '/dashboard/profile.php');
        }
    }
}

// Refresh user data
$user = getCurrentUser();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/styles.css">
</head>
<body class="dashboard-body">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <header class="dashboard-header" role="banner">
        <div class="dashboard-header__inner">
            <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
                <span class="logo__icon" aria-hidden="true">$</span>
                <span>ExpenseTracker</span>
            </a>
            
            <nav class="dashboard-nav" role="navigation" aria-label="Dashboard navigation">
                <a href="<?php echo SITE_URL; ?>/dashboard/" class="dashboard-nav__link">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/expenses.php" class="dashboard-nav__link">Expenses</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/add-expense.php" class="dashboard-nav__link">Add Expense</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/reports.php" class="dashboard-nav__link">Reports</a>
            </nav>
            
            <div class="dashboard-user">
                <span class="dashboard-user__name"><?php echo htmlspecialchars($user['first_name']); ?></span>
            </div>
        </div>
    </header>
    
    <main id="main-content" class="dashboard-main">
        <div class="container">
            <?php echo displayFlashMessage(); ?>
            
            <div class="page-header">
                <h1>My Profile</h1>
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
            
            <div class="dashboard-grid">
                <!-- Profile Information -->
                <section class="dashboard-card" aria-labelledby="profile-title">
                    <div class="dashboard-card__header">
                        <h2 id="profile-title">Profile Information</h2>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" class="form-input" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="form-hint">Username cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" class="form-input" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small class="form-hint">Email cannot be changed</small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name <span class="required">*</span></label>
                                <input type="text" id="first_name" name="first_name" class="form-input"
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>"
                                       required minlength="2" maxlength="50">
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name <span class="required">*</span></label>
                                <input type="text" id="last_name" name="last_name" class="form-input"
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>"
                                       required minlength="2" maxlength="50">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-input"
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                   placeholder="Optional">
                        </div>
                        
                        <button type="submit" class="btn btn--primary">Save Changes</button>
                    </form>
                </section>
                
                <!-- Account Information & Password Change -->
                <div>
                    <!-- Account Info -->
                    <section class="dashboard-card" style="margin-bottom: var(--spacing-lg);" aria-labelledby="account-title">
                        <div class="dashboard-card__header">
                            <h2 id="account-title">Account Information</h2>
                        </div>
                        
                        <div class="profile-info">
                            <div class="profile-info__item">
                                <span class="profile-info__label">Account Type</span>
                                <span class="role-badge role-badge--<?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </div>
                            <div class="profile-info__item">
                                <span class="profile-info__label">Status</span>
                                <span class="status-badge status-badge--<?php echo $user['status']; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </div>
                            <div class="profile-info__item">
                                <span class="profile-info__label">Member Since</span>
                                <span><?php echo formatDate($user['created_at']); ?></span>
                            </div>
                            <div class="profile-info__item">
                                <span class="profile-info__label">Last Login</span>
                                <span><?php echo $user['last_login'] ? formatDate($user['last_login'], 'd M Y H:i') : 'N/A'; ?></span>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Change Password -->
                    <section class="dashboard-card" aria-labelledby="password-title">
                        <div class="dashboard-card__header">
                            <h2 id="password-title">Change Password</h2>
                        </div>
                        
                        <?php if (isset($_SESSION['password_change_pending']) && $_SESSION['password_change_pending']): ?>
                            <!-- Step 2: Enter verification code and new password -->
                            <div class="alert alert--info" style="margin-bottom: 1rem;">
                                A verification code has been sent to <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                            </div>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                <input type="hidden" name="action" value="verify_and_change_password">
                                
                                <div class="form-group">
                                    <label for="verification_code" class="form-label">Verification Code <span class="required">*</span></label>
                                    <input type="text" id="verification_code" name="verification_code" 
                                           class="form-input" required maxlength="6" pattern="\d{6}"
                                           placeholder="000000" autocomplete="one-time-code"
                                           style="font-size: 1.25rem; letter-spacing: 0.3rem; text-align: center; font-family: monospace;">
                                    <small class="form-hint">Enter the 6-digit code from your email</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password" class="form-label">New Password <span class="required">*</span></label>
                                    <input type="password" id="new_password" name="new_password" 
                                           class="form-input" required autocomplete="new-password">
                                    <small class="form-hint">8+ characters with uppercase, lowercase, number & special character</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">Confirm New Password <span class="required">*</span></label>
                                    <input type="password" id="confirm_password" name="confirm_password" 
                                           class="form-input" required autocomplete="new-password">
                                </div>
                                
                                <div style="display: flex; gap: 1rem;">
                                    <button type="submit" class="btn btn--primary">Verify & Change Password</button>
                                    <button type="submit" name="action" value="cancel_password_change" class="btn btn--secondary">Cancel</button>
                                </div>
                            </form>
                            
                            <div style="margin-top: 1rem; text-align: center;">
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="action" value="cancel_password_change">
                                    <small>Didn't receive the code? Cancel and try again.</small>
                                </form>
                            </div>
                            
                            <script>
                                document.getElementById('verification_code').addEventListener('input', function(e) {
                                    this.value = this.value.replace(/\D/g, '').substring(0, 6);
                                });
                            </script>
                        <?php else: ?>
                            <!-- Step 1: Verify current password -->
                            <p style="margin-bottom: 1rem; color: var(--medium-gray);">
                                To change your password, first verify your current password. A verification code will be sent to your email.
                            </p>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                <input type="hidden" name="action" value="request_password_change">
                                
                                <div class="form-group">
                                    <label for="current_password" class="form-label">Current Password <span class="required">*</span></label>
                                    <input type="password" id="current_password" name="current_password" 
                                           class="form-input" required autocomplete="current-password">
                                </div>
                                
                                <button type="submit" class="btn btn--secondary">Send Verification Code</button>
                            </form>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date('Y'); ?> Smart Expense Tracker | Student ID: 20034038</p>
    </footer>
    
    <style>
        .profile-info__item {
            display: flex;
            justify-content: space-between;
            padding: var(--spacing-md) 0;
            border-bottom: 1px solid #eee;
        }
        .profile-info__item:last-child {
            border-bottom: none;
        }
        .profile-info__label {
            color: var(--medium-gray);
        }
    </style>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
