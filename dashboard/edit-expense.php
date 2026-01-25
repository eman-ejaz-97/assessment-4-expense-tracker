<?php
/**
 * Smart Expense Tracking System - Edit Expense
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Form for editing existing expense records
 * Implements CRUD (Update) operation
 */

$pageTitle = 'Edit Expense | Smart Expense Tracker';
$pageDescription = 'Edit your expense record.';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Require authentication
requireLogin();

$userId = $_SESSION['user_id'];
$user = getCurrentUser();
$categories = getCategories($userId);

// Get expense ID from URL
$expenseId = intval($_GET['id'] ?? 0);

if ($expenseId <= 0) {
    setFlashMessage('error', 'Invalid expense ID.');
    redirect(SITE_URL . '/dashboard/expenses.php');
}

// Fetch expense
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM expenses WHERE expense_id = ? AND user_id = ?");
$stmt->execute([$expenseId, $userId]);
$expense = $stmt->fetch();

if (!$expense) {
    setFlashMessage('error', 'Expense not found or you do not have permission to edit it.');
    redirect(SITE_URL . '/dashboard/expenses.php');
}

$errors = [];
$formData = [
    'category_id' => $expense['category_id'],
    'amount' => $expense['amount'],
    'description' => $expense['description'],
    'expense_date' => $expense['expense_date'],
    'payment_method' => $expense['payment_method'],
    'notes' => $expense['notes']
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        
        // Validation (same as add-expense.php)
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
        }
        
        if (empty($formData['expense_date'])) {
            $errors[] = 'Please select a date.';
        } elseif (strtotime($formData['expense_date']) > time()) {
            $errors[] = 'Expense date cannot be in the future.';
        }
        
        // Update expense
        if (empty($errors)) {
            $stmt = $pdo->prepare("
                UPDATE expenses 
                SET category_id = ?, amount = ?, description = ?, expense_date = ?, 
                    payment_method = ?, notes = ?, updated_at = NOW()
                WHERE expense_id = ? AND user_id = ?
            ");
            
            try {
                $stmt->execute([
                    $formData['category_id'],
                    $formData['amount'],
                    $formData['description'],
                    $formData['expense_date'],
                    $formData['payment_method'],
                    $formData['notes'] ?: null,
                    $expenseId,
                    $userId
                ]);
                
                logActivity('EDIT_EXPENSE', 'Updated expense ID: ' . $expenseId);
                
                setFlashMessage('success', 'Expense updated successfully!');
                redirect(SITE_URL . '/dashboard/expenses.php');
                
            } catch (PDOException $e) {
                error_log("Edit Expense Error: " . $e->getMessage());
                $errors[] = 'An error occurred while updating the expense. Please try again.';
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
    
    <header class="dashboard-header" role="banner">
        <div class="dashboard-header__inner">
            <a href="<?php echo SITE_URL; ?>/index.php" class="logo" aria-label="Smart Expense Tracker Home">
                <span class="logo__icon" aria-hidden="true">$</span>
                <span>ExpenseTracker</span>
            </a>
            
            <nav class="dashboard-nav" role="navigation" aria-label="Dashboard navigation">
                <a href="<?php echo SITE_URL; ?>/dashboard/" class="dashboard-nav__link">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/expenses.php" class="dashboard-nav__link active">Expenses</a>
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
            <div class="page-header">
                <h1>Edit Expense</h1>
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
                            <select id="category_id" name="category_id" class="form-select" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>" 
                                            <?php echo ($formData['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo $cat['category_icon'] . ' ' . htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount" class="form-label">
                                Amount ($) <span class="required">*</span>
                            </label>
                            <input type="number" id="amount" name="amount" class="form-input"
                                   value="<?php echo htmlspecialchars($formData['amount']); ?>"
                                   min="0.01" max="9999999.99" step="0.01" required placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">
                            Description <span class="required">*</span>
                        </label>
                        <input type="text" id="description" name="description" class="form-input"
                               value="<?php echo htmlspecialchars($formData['description']); ?>"
                               required minlength="3" maxlength="255"
                               placeholder="What was this expense for?">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expense_date" class="form-label">
                                Date <span class="required">*</span>
                            </label>
                            <input type="date" id="expense_date" name="expense_date" class="form-input"
                                   value="<?php echo htmlspecialchars($formData['expense_date']); ?>"
                                   max="<?php echo date('Y-m-d'); ?>" required>
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
                                  placeholder="Add any additional notes..."><?php echo htmlspecialchars($formData['notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn--primary btn--lg">
                            💾 Update Expense
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
