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
        } elseif ($action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Verify current password
            if (!verifyPassword($currentPassword, $user['password_hash'])) {
                $errors[] = 'Current password is incorrect.';
            }
            
            // Validate new password
            $passwordValidation = validatePassword($newPassword);
            if (!$passwordValidation['valid']) {
                $errors[] = $passwordValidation['message'];
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'New passwords do not match.';
            }
            
            if (empty($errors)) {
                $pdo = getDBConnection();
                $hashedPassword = hashPassword($newPassword);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?");
                $stmt->execute([$hashedPassword, $userId]);
                
                logActivity('PASSWORD_CHANGE', 'Changed account password');
                setFlashMessage('success', 'Password changed successfully!');
                redirect(SITE_URL . '/dashboard/profile.php');
            }
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
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-group">
                                <label for="current_password" class="form-label">Current Password <span class="required">*</span></label>
                                <input type="password" id="current_password" name="current_password" 
                                       class="form-input" required autocomplete="current-password">
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
                            
                            <button type="submit" class="btn btn--secondary">Change Password</button>
                        </form>
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
