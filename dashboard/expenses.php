<?php
/**
 * Smart Expense Tracking System - Expenses List
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * View all expenses with filtering, pagination, and CRUD operations
 * Implements Read, Update, Delete operations
 */

$pageTitle = 'My Expenses | Smart Expense Tracker';
$pageDescription = 'View and manage all your expense records.';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Require authentication
requireLogin();

$userId = $_SESSION['user_id'];
$user = getCurrentUser();
$categories = getCategories($userId);

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $expenseId = intval($_POST['expense_id'] ?? 0);
        
        if ($expenseId > 0) {
            $pdo = getDBConnection();
            
            // Verify expense belongs to user
            $stmt = $pdo->prepare("SELECT expense_id FROM expenses WHERE expense_id = ? AND user_id = ?");
            $stmt->execute([$expenseId, $userId]);
            
            if ($stmt->fetch()) {
                $stmt = $pdo->prepare("DELETE FROM expenses WHERE expense_id = ? AND user_id = ?");
                $stmt->execute([$expenseId, $userId]);
                
                logActivity('DELETE_EXPENSE', 'Deleted expense ID: ' . $expenseId);
                setFlashMessage('success', 'Expense deleted successfully.');
            }
        }
    }
    redirect(SITE_URL . '/dashboard/expenses.php');
}

// Get filters
$filters = [];
if (!empty($_GET['category'])) {
    $filters['category_id'] = intval($_GET['category']);
}
if (!empty($_GET['start_date'])) {
    $filters['start_date'] = $_GET['start_date'];
}
if (!empty($_GET['end_date'])) {
    $filters['end_date'] = $_GET['end_date'];
}

// Get expenses
$expenses = getUserExpenses($userId, $filters);

// Calculate totals
$totalAmount = array_sum(array_column($expenses, 'amount'));

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
                <div class="dashboard-user__dropdown">
                    <a href="<?php echo SITE_URL; ?>/dashboard/profile.php">Profile</a>
                    <a href="<?php echo SITE_URL; ?>/auth/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <main id="main-content" class="dashboard-main">
        <div class="container">
            <?php echo displayFlashMessage(); ?>
            
            <div class="page-header">
                <h1>My Expenses</h1>
                <a href="<?php echo SITE_URL; ?>/dashboard/add-expense.php" class="btn btn--primary">
                    + Add New Expense
                </a>
            </div>
            
            <!-- Filters -->
            <div class="filter-card">
                <form method="GET" action="" class="filter-form">
                    <div class="filter-form__group">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>"
                                        <?php echo (isset($filters['category_id']) && $filters['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo $cat['category_icon'] . ' ' . htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-form__group">
                        <label for="start_date" class="form-label">From Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-input"
                               value="<?php echo htmlspecialchars($filters['start_date'] ?? ''); ?>">
                    </div>
                    
                    <div class="filter-form__group">
                        <label for="end_date" class="form-label">To Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-input"
                               value="<?php echo htmlspecialchars($filters['end_date'] ?? ''); ?>">
                    </div>
                    
                    <div class="filter-form__actions">
                        <button type="submit" class="btn btn--primary btn--sm">Apply Filter</button>
                        <a href="<?php echo SITE_URL; ?>/dashboard/expenses.php" class="btn btn--secondary btn--sm">Clear</a>
                    </div>
                </form>
            </div>
            
            <!-- Summary -->
            <div class="summary-bar">
                <span><strong><?php echo count($expenses); ?></strong> expense(s) found</span>
                <span>Total: <strong><?php echo formatCurrency($totalAmount); ?></strong></span>
            </div>
            
            <!-- Expenses Table -->
            <?php if (empty($expenses)): ?>
                <div class="empty-state">
                    <div class="empty-state__icon">📝</div>
                    <h3>No Expenses Found</h3>
                    <p>Start tracking your expenses by adding your first one.</p>
                    <a href="<?php echo SITE_URL; ?>/dashboard/add-expense.php" class="btn btn--primary">
                        Add Your First Expense
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table" role="table">
                        <caption class="sr-only">List of your expenses</caption>
                        <thead>
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Category</th>
                                <th scope="col">Description</th>
                                <th scope="col">Payment</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td data-label="Date"><?php echo formatDate($expense['expense_date']); ?></td>
                                    <td data-label="Category">
                                        <span class="category-badge" style="background-color: <?php echo htmlspecialchars($expense['category_color']); ?>20; color: <?php echo htmlspecialchars($expense['category_color']); ?>">
                                            <?php echo $expense['category_icon'] . ' ' . htmlspecialchars($expense['category_name']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Description"><?php echo htmlspecialchars($expense['description']); ?></td>
                                    <td data-label="Payment"><?php echo ucfirst(str_replace('_', ' ', $expense['payment_method'])); ?></td>
                                    <td data-label="Amount" class="amount-cell">
                                        <?php echo formatCurrency($expense['amount']); ?>
                                    </td>
                                    <td data-label="Actions" class="actions-cell">
                                        <a href="<?php echo SITE_URL; ?>/dashboard/edit-expense.php?id=<?php echo $expense['expense_id']; ?>" 
                                           class="btn btn--sm btn--secondary" title="Edit">✏️ Edit</a>
                                        <form method="POST" action="" class="inline-form" 
                                              onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="expense_id" value="<?php echo $expense['expense_id']; ?>">
                                            <button type="submit" class="btn btn--sm btn--danger" title="Delete">🗑️ Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"><strong>Total</strong></td>
                                <td class="amount-cell"><strong><?php echo formatCurrency($totalAmount); ?></strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date('Y'); ?> Smart Expense Tracker | Student ID: 20034038</p>
    </footer>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
