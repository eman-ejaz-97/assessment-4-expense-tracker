<?php
/**
 * Smart Expense Tracking System - Budget Management
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Manage monthly budgets per category with spending tracking
 * and alert thresholds
 */

$pageTitle = 'Budgets | Smart Expense Tracker';
$pageDescription = 'Set and manage your monthly budgets by category.';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Require authentication
requireLogin();

$userId = $_SESSION['user_id'];
$user = getCurrentUser();
$pdo = getDBConnection();

// Get current month/year or from query params
$currentMonth = isset($_GET['month']) ? intval($_GET['month']) : intval(date('n'));
$currentYear = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Validate month/year
if ($currentMonth < 1 || $currentMonth > 12) $currentMonth = intval(date('n'));
if ($currentYear < 2020 || $currentYear > 2030) $currentYear = intval(date('Y'));

$monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
               'July', 'August', 'September', 'October', 'November', 'December'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request. Please try again.');
        redirect(SITE_URL . '/dashboard/budgets.php');
    }
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_budget') {
        $categoryId = intval($_POST['category_id'] ?? 0);
        $budgetAmount = floatval($_POST['budget_amount'] ?? 0);
        $alertThreshold = intval($_POST['alert_threshold'] ?? 80);
        $budgetMonth = intval($_POST['month'] ?? $currentMonth);
        $budgetYear = intval($_POST['year'] ?? $currentYear);
        
        if ($categoryId > 0 && $budgetAmount > 0) {
            // Check if budget exists for this category/month/year
            $stmt = $pdo->prepare("
                SELECT budget_id FROM budgets 
                WHERE user_id = ? AND category_id = ? AND month = ? AND year = ?
            ");
            $stmt->execute([$userId, $categoryId, $budgetMonth, $budgetYear]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing budget
                $stmt = $pdo->prepare("
                    UPDATE budgets 
                    SET budget_amount = ?, alert_threshold = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE budget_id = ?
                ");
                $stmt->execute([$budgetAmount, $alertThreshold, $existing['budget_id']]);
                setFlashMessage('success', 'Budget updated successfully.');
            } else {
                // Insert new budget
                $stmt = $pdo->prepare("
                    INSERT INTO budgets (user_id, category_id, budget_amount, month, year, alert_threshold)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $categoryId, $budgetAmount, $budgetMonth, $budgetYear, $alertThreshold]);
                setFlashMessage('success', 'Budget created successfully.');
            }
            
            logActivity('SET_BUDGET', 'Set budget for category ID: ' . $categoryId);
        } else {
            setFlashMessage('error', 'Please enter a valid budget amount.');
        }
    } elseif ($action === 'delete_budget') {
        $budgetId = intval($_POST['budget_id'] ?? 0);
        
        if ($budgetId > 0) {
            $stmt = $pdo->prepare("DELETE FROM budgets WHERE budget_id = ? AND user_id = ?");
            $stmt->execute([$budgetId, $userId]);
            setFlashMessage('success', 'Budget removed successfully.');
            logActivity('DELETE_BUDGET', 'Deleted budget ID: ' . $budgetId);
        }
    }
    
    redirect(SITE_URL . '/dashboard/budgets.php?month=' . $currentMonth . '&year=' . $currentYear);
}

// Get categories
$categories = getCategories($userId);

// Get budgets for current month/year with spending
$stmt = $pdo->prepare("
    SELECT 
        c.category_id,
        c.category_name,
        c.category_icon,
        c.category_color,
        b.budget_id,
        b.budget_amount,
        b.alert_threshold,
        COALESCE(SUM(e.amount), 0) as spent
    FROM expense_categories c
    LEFT JOIN budgets b ON c.category_id = b.category_id 
        AND b.user_id = ? AND b.month = ? AND b.year = ?
    LEFT JOIN expenses e ON c.category_id = e.category_id 
        AND e.user_id = ? 
        AND EXTRACT(MONTH FROM e.expense_date) = ? 
        AND EXTRACT(YEAR FROM e.expense_date) = ?
    WHERE c.is_default = TRUE OR c.user_id = ?
    GROUP BY c.category_id, c.category_name, c.category_icon, c.category_color, 
             b.budget_id, b.budget_amount, b.alert_threshold
    ORDER BY c.category_name
");
$stmt->execute([$userId, $currentMonth, $currentYear, $userId, $currentMonth, $currentYear, $userId]);
$budgetData = $stmt->fetchAll();

// Calculate totals
$totalBudget = 0;
$totalSpent = 0;
foreach ($budgetData as $item) {
    if ($item['budget_amount']) {
        $totalBudget += $item['budget_amount'];
    }
    $totalSpent += $item['spent'];
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
                <a href="<?php echo SITE_URL; ?>/dashboard/expenses.php" class="dashboard-nav__link">Expenses</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/add-expense.php" class="dashboard-nav__link">Add Expense</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/budgets.php" class="dashboard-nav__link active">Budgets</a>
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
                <h1>Budget Management</h1>
            </div>
            
            <!-- Month Navigation -->
            <div class="filter-card">
                <form method="GET" action="" class="filter-form">
                    <div class="filter-form__group">
                        <label for="month" class="form-label">Month</label>
                        <select id="month" name="month" class="form-select">
                            <?php foreach ($monthNames as $idx => $name): ?>
                                <option value="<?php echo $idx + 1; ?>" 
                                        <?php echo ($currentMonth == $idx + 1) ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-form__group">
                        <label for="year" class="form-label">Year</label>
                        <select id="year" name="year" class="form-select">
                            <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                                <option value="<?php echo $y; ?>" 
                                        <?php echo ($currentYear == $y) ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="filter-form__actions">
                        <button type="submit" class="btn btn--primary btn--sm">View Month</button>
                    </div>
                </form>
            </div>
            
            <!-- Budget Summary -->
            <section class="dashboard-stats" aria-label="Budget statistics">
                <div class="stat-card stat-card--primary">
                    <div class="stat-card__icon">💵</div>
                    <div class="stat-card__content">
                        <h3 class="stat-card__value"><?php echo formatCurrency($totalBudget); ?></h3>
                        <p class="stat-card__label">Total Budget</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card__icon">💰</div>
                    <div class="stat-card__content">
                        <h3 class="stat-card__value"><?php echo formatCurrency($totalSpent); ?></h3>
                        <p class="stat-card__label">Total Spent</p>
                    </div>
                </div>
                
                <div class="stat-card <?php echo ($totalBudget > 0 && $totalSpent > $totalBudget) ? 'stat-card--danger' : ''; ?>">
                    <div class="stat-card__icon">📊</div>
                    <div class="stat-card__content">
                        <h3 class="stat-card__value"><?php echo formatCurrency(max(0, $totalBudget - $totalSpent)); ?></h3>
                        <p class="stat-card__label">Remaining</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card__icon">📅</div>
                    <div class="stat-card__content">
                        <h3 class="stat-card__value"><?php echo $monthNames[$currentMonth - 1]; ?></h3>
                        <p class="stat-card__label"><?php echo $currentYear; ?></p>
                    </div>
                </div>
            </section>
            
            <!-- Budget Cards by Category -->
            <section class="budget-grid" aria-labelledby="budgets-title">
                <h2 id="budgets-title" class="section-title">Category Budgets</h2>
                
                <div class="budget-cards">
                    <?php foreach ($budgetData as $item): ?>
                        <?php 
                        $hasBudget = !empty($item['budget_amount']);
                        $spent = floatval($item['spent']);
                        $budget = floatval($item['budget_amount'] ?? 0);
                        $threshold = intval($item['alert_threshold'] ?? 80);
                        $percentage = $budget > 0 ? min(100, ($spent / $budget) * 100) : 0;
                        $isOverBudget = $budget > 0 && $spent > $budget;
                        $isNearThreshold = $budget > 0 && $percentage >= $threshold && !$isOverBudget;
                        ?>
                        <div class="budget-card <?php echo $isOverBudget ? 'budget-card--danger' : ($isNearThreshold ? 'budget-card--warning' : ''); ?>">
                            <div class="budget-card__header">
                                <span class="budget-card__icon" style="background-color: <?php echo htmlspecialchars($item['category_color']); ?>">
                                    <?php echo $item['category_icon']; ?>
                                </span>
                                <h3 class="budget-card__title"><?php echo htmlspecialchars($item['category_name']); ?></h3>
                                <?php if ($isOverBudget): ?>
                                    <span class="budget-alert budget-alert--danger" title="Over budget!">⚠️</span>
                                <?php elseif ($isNearThreshold): ?>
                                    <span class="budget-alert budget-alert--warning" title="Approaching budget limit">⚡</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="budget-card__body">
                                <?php if ($hasBudget): ?>
                                    <div class="budget-progress">
                                        <div class="budget-progress__bar">
                                            <div class="budget-progress__fill <?php echo $isOverBudget ? 'budget-progress__fill--danger' : ($isNearThreshold ? 'budget-progress__fill--warning' : ''); ?>" 
                                                 style="width: <?php echo min(100, $percentage); ?>%"></div>
                                        </div>
                                        <div class="budget-progress__text">
                                            <span><?php echo formatCurrency($spent); ?> spent</span>
                                            <span><?php echo formatCurrency($budget); ?> budget</span>
                                        </div>
                                        <p class="budget-progress__percent">
                                            <?php echo number_format($percentage, 0); ?>% used
                                            <?php if ($budget > $spent): ?>
                                                • <?php echo formatCurrency($budget - $spent); ?> left
                                            <?php else: ?>
                                                • <?php echo formatCurrency($spent - $budget); ?> over
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <p class="budget-card__empty">
                                        <?php if ($spent > 0): ?>
                                            <?php echo formatCurrency($spent); ?> spent (no budget set)
                                        <?php else: ?>
                                            No budget set
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="budget-card__footer">
                                <button type="button" class="btn btn--sm btn--secondary" 
                                        onclick="openBudgetModal(<?php echo $item['category_id']; ?>, '<?php echo htmlspecialchars($item['category_name']); ?>', <?php echo $budget; ?>, <?php echo $threshold; ?>)">
                                    <?php echo $hasBudget ? '✏️ Edit' : '➕ Set Budget'; ?>
                                </button>
                                <?php if ($hasBudget): ?>
                                    <form method="POST" action="" class="inline-form" 
                                          onsubmit="return confirm('Remove this budget?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="action" value="delete_budget">
                                        <input type="hidden" name="budget_id" value="<?php echo $item['budget_id']; ?>">
                                        <button type="submit" class="btn btn--sm btn--outline">🗑️ Remove</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Budget Modal -->
    <div id="budgetModal" class="modal" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal__overlay" onclick="closeBudgetModal()"></div>
        <div class="modal__content">
            <button type="button" class="modal__close" onclick="closeBudgetModal()" aria-label="Close modal">&times;</button>
            <h2 id="modalTitle" class="modal__title">Set Budget</h2>
            
            <form method="POST" action="" class="form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="save_budget">
                <input type="hidden" name="month" value="<?php echo $currentMonth; ?>">
                <input type="hidden" name="year" value="<?php echo $currentYear; ?>">
                <input type="hidden" id="modal_category_id" name="category_id" value="">
                
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <p id="modal_category_name" class="form-static"></p>
                </div>
                
                <div class="form-group">
                    <label for="modal_budget_amount" class="form-label">Budget Amount ($)</label>
                    <input type="number" id="modal_budget_amount" name="budget_amount" 
                           class="form-input" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="modal_alert_threshold" class="form-label">Alert Threshold (%)</label>
                    <input type="number" id="modal_alert_threshold" name="alert_threshold" 
                           class="form-input" min="50" max="100" value="80">
                    <small class="form-hint">You'll see a warning when spending reaches this percentage.</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn--secondary" onclick="closeBudgetModal()">Cancel</button>
                    <button type="submit" class="btn btn--primary">Save Budget</button>
                </div>
            </form>
        </div>
    </div>
    
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date('Y'); ?> Smart Expense Tracker | Student ID: 20034038</p>
    </footer>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
    <script>
        function openBudgetModal(categoryId, categoryName, budget, threshold) {
            document.getElementById('modal_category_id').value = categoryId;
            document.getElementById('modal_category_name').textContent = categoryName;
            document.getElementById('modal_budget_amount').value = budget > 0 ? budget : '';
            document.getElementById('modal_alert_threshold').value = threshold || 80;
            document.getElementById('budgetModal').classList.add('modal--open');
            document.getElementById('budgetModal').setAttribute('aria-hidden', 'false');
            document.getElementById('modal_budget_amount').focus();
        }
        
        function closeBudgetModal() {
            document.getElementById('budgetModal').classList.remove('modal--open');
            document.getElementById('budgetModal').setAttribute('aria-hidden', 'true');
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeBudgetModal();
            }
        });
    </script>
</body>
</html>
