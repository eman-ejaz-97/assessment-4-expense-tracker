<?php
/**
 * Smart Expense Tracking System - Add New Expense
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Form for adding new expense records with validation
 * Implements CRUD (Create) operation
 */

$pageTitle = 'Add Expense | Smart Expense Tracker';
$pageDescription = 'Add a new expense to track your spending.';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Require authentication
requireLogin();

$userId = $_SESSION['user_id'];
$user = getCurrentUser();
$categories = getCategories($userId);

$errors = [];
$success = false;
$formData = [
    'category_id' => '',
    'amount' => '',
    'description' => '',
    'expense_date' => date('Y-m-d'),
    'payment_method' => 'cash',
    'notes' => ''
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Collect and sanitize form data
        $formData['category_id'] = intval($_POST['category_id'] ?? 0);
        $formData['amount'] = floatval($_POST['amount'] ?? 0);
        $formData['description'] = sanitizeInput($_POST['description'] ?? '');
        $formData['expense_date'] = $_POST['expense_date'] ?? date('Y-m-d');
        $formData['payment_method'] = sanitizeInput($_POST['payment_method'] ?? 'cash');
        $formData['notes'] = sanitizeInput($_POST['notes'] ?? '');
        
        // Validation
        if (empty($formData['category_id'])) {
            $errors[] = 'Please select a category.';
        }
        
        if ($formData['amount'] <= 0) {
            $errors[] = 'Please enter a valid amount greater than 0.';
        } elseif ($formData['amount'] > 9999999.99) {
            $errors[] = 'Amount exceeds maximum allowed value.';
        }
        
        if (empty($formData['description'])) {
            $errors[] = 'Please enter a description.';
        } elseif (strlen($formData['description']) < 3) {
            $errors[] = 'Description must be at least 3 characters.';
        } elseif (strlen($formData['description']) > 255) {
            $errors[] = 'Description must be less than 255 characters.';
        }
        
        if (empty($formData['expense_date'])) {
            $errors[] = 'Please select a date.';
        } elseif (strtotime($formData['expense_date']) > time()) {
            $errors[] = 'Expense date cannot be in the future.';
        }
        
        $validPaymentMethods = ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'paypal', 'other'];
        if (!in_array($formData['payment_method'], $validPaymentMethods)) {
            $errors[] = 'Invalid payment method selected.';
        }
        
        // Insert expense
        if (empty($errors)) {
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("
                INSERT INTO expenses (user_id, category_id, amount, description, expense_date, payment_method, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            try {
                $stmt->execute([
                    $userId,
                    $formData['category_id'],
                    $formData['amount'],
                    $formData['description'],
                    $formData['expense_date'],
                    $formData['payment_method'],
                    $formData['notes'] ?: null
                ]);
                
                logActivity('ADD_EXPENSE', 'Added expense: ' . $formData['description'] . ' - $' . $formData['amount']);
                
                setFlashMessage('success', 'Expense added successfully!');
                redirect(SITE_URL . '/dashboard/expenses.php');
                
            } catch (PDOException $e) {
                error_log("Add Expense Error: " . $e->getMessage());
                $errors[] = 'An error occurred while saving the expense. Please try again.';
            }
        }
    }
}

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
    
    <!-- Dashboard Header -->
    <header class="dashboard-header" role="banner">
        <div class="dashboard-header__inner">
            <a href="<?php echo SITE_URL; ?>/index.php" class="logo" aria-label="Smart Expense Tracker Home">
                <span class="logo__icon" aria-hidden="true">$</span>
                <span>ExpenseTracker</span>
            </a>
            
            <nav class="dashboard-nav" role="navigation" aria-label="Dashboard navigation">
                <a href="<?php echo SITE_URL; ?>/dashboard/" class="dashboard-nav__link">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/expenses.php" class="dashboard-nav__link">Expenses</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/add-expense.php" class="dashboard-nav__link active">Add Expense</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/reports.php" class="dashboard-nav__link">Reports</a>
            </nav>
            
            <div class="dashboard-user">
                <span class="dashboard-user__name"><?php echo htmlspecialchars($user['first_name']); ?></span>
                <div class="dashboard-user__dropdown">
                    <a href="<?php echo SITE_URL; ?>/dashboard/profile.php">Profile</a>
                    <a href="<?php echo SITE_URL; ?>/auth/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <main id="main-content" class="dashboard-main">
        <div class="container">
            <div class="page-header">
                <h1>Add New Expense</h1>
                <a href="<?php echo SITE_URL; ?>/dashboard/expenses.php" class="btn btn--secondary btn--sm">
                    ← Back to Expenses
                </a>
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
            
            <div class="form-card">
                <form method="POST" action="" class="expense-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id" class="form-label">
                                Category <span class="required">*</span>
                            </label>
                            <select id="category_id" name="category_id" class="form-select" required aria-describedby="category-error">
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>" 
                                            <?php echo ($formData['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo $cat['category_icon'] . ' ' . htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="form-error" id="category-error" role="alert"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount" class="form-label">
                                Amount ($) <span class="required">*</span>
                            </label>
                            <input type="number" id="amount" name="amount" class="form-input"
                                   value="<?php echo htmlspecialchars($formData['amount']); ?>"
                                   min="0.01" max="9999999.99" step="0.01" required
                                   placeholder="0.00" aria-describedby="amount-error">
                            <span class="form-error" id="amount-error" role="alert"></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">
                            Description <span class="required">*</span>
                        </label>
                        <input type="text" id="description" name="description" class="form-input"
                               value="<?php echo htmlspecialchars($formData['description']); ?>"
                               required minlength="3" maxlength="255"
                               placeholder="What was this expense for?" aria-describedby="description-error">
                        <span class="form-error" id="description-error" role="alert"></span>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expense_date" class="form-label">
                                Date <span class="required">*</span>
                            </label>
                            <input type="date" id="expense_date" name="expense_date" class="form-input"
                                   value="<?php echo htmlspecialchars($formData['expense_date']); ?>"
                                   max="<?php echo date('Y-m-d'); ?>" required aria-describedby="date-error">
                            <span class="form-error" id="date-error" role="alert"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_method" class="form-label">
                                Payment Method <span class="required">*</span>
                            </label>
                            <select id="payment_method" name="payment_method" class="form-select" required>
                                <option value="cash" <?php echo ($formData['payment_method'] === 'cash') ? 'selected' : ''; ?>>💵 Cash</option>
                                <option value="credit_card" <?php echo ($formData['payment_method'] === 'credit_card') ? 'selected' : ''; ?>>💳 Credit Card</option>
                                <option value="debit_card" <?php echo ($formData['payment_method'] === 'debit_card') ? 'selected' : ''; ?>>💳 Debit Card</option>
                                <option value="bank_transfer" <?php echo ($formData['payment_method'] === 'bank_transfer') ? 'selected' : ''; ?>>🏦 Bank Transfer</option>
                                <option value="paypal" <?php echo ($formData['payment_method'] === 'paypal') ? 'selected' : ''; ?>>📱 PayPal</option>
                                <option value="other" <?php echo ($formData['payment_method'] === 'other') ? 'selected' : ''; ?>>📦 Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea id="notes" name="notes" class="form-textarea" 
                                  rows="3" maxlength="1000"
                                  placeholder="Add any additional notes..."><?php echo htmlspecialchars($formData['notes']); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn--primary btn--lg">
                            💾 Save Expense
                        </button>
                        <a href="<?php echo SITE_URL; ?>/dashboard/expenses.php" class="btn btn--secondary btn--lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date('Y'); ?> Smart Expense Tracker | Student ID: 20034038</p>
    </footer>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
