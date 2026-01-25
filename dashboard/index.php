<?php
/**
 * Smart Expense Tracking System - User Dashboard
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Main dashboard displaying expense summary, recent transactions,
 * and quick access to key features
 */

$pageTitle = 'Dashboard | Smart Expense Tracker';
$pageDescription = 'View your expense summary, recent transactions, and manage your finances.';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Require authentication
requireLogin();

$userId = $_SESSION['user_id'];
$user = getCurrentUser();

// Get spending summary
$summary = getSpendingSummary($userId);
$categories = getCategories($userId);

// Get month names
$monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
               'July', 'August', 'September', 'October', 'November', 'December'];
$currentMonth = $monthNames[$summary['month'] - 1] . ' ' . $summary['year'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
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
                <a href="<?php echo SITE_URL; ?>/dashboard/" class="dashboard-nav__link active">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/expenses.php" class="dashboard-nav__link">Expenses</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/add-expense.php" class="dashboard-nav__link">Add Expense</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/reports.php" class="dashboard-nav__link">Reports</a>
                <?php if (hasRole(['admin', 'member'])): ?>
                    <a href="<?php echo SITE_URL; ?>/admin/" class="dashboard-nav__link">Admin</a>
                <?php endif; ?>
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
            
            <!-- Welcome Section -->
            <section class="dashboard-welcome" aria-labelledby="welcome-title">
                <div class="dashboard-welcome__content">
                    <h1 id="welcome-title">Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                    <p>Here's your financial overview for <?php echo $currentMonth; ?></p>
                </div>
                <a href="<?php echo SITE_URL; ?>/dashboard/add-expense.php" class="btn btn--primary">
                    + Add New Expense
                </a>
            </section>
            
            <!-- Stats Cards -->
            <section class="dashboard-stats" aria-label="Spending statistics">
                <div class="stat-card stat-card--primary">
                    <div class="stat-card__icon">💰</div>
                    <div class="stat-card__content">
                        <h3 class="stat-card__value"><?php echo formatCurrency($summary['total_spending']); ?></h3>
                        <p class="stat-card__label">Total Spending</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card__icon">📊</div>
                    <div class="stat-card__content">
                        <h3 class="stat-card__value"><?php echo count($summary['recent_expenses']); ?></h3>
                        <p class="stat-card__label">Recent Transactions</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card__icon">📁</div>
                    <div class="stat-card__content">
                        <h3 class="stat-card__value"><?php echo count($categories); ?></h3>
                        <p class="stat-card__label">Categories</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card__icon">👤</div>
                    <div class="stat-card__content">
                        <h3 class="stat-card__value"><?php echo ucfirst($user['role']); ?></h3>
                        <p class="stat-card__label">Account Type</p>
                    </div>
                </div>
            </section>
            
            <div class="dashboard-grid">
                <!-- Recent Transactions -->
                <section class="dashboard-card" aria-labelledby="recent-title">
                    <div class="dashboard-card__header">
                        <h2 id="recent-title">Recent Transactions</h2>
                        <a href="<?php echo SITE_URL; ?>/dashboard/expenses.php" class="dashboard-card__link">View All</a>
                    </div>
                    
                    <?php if (empty($summary['recent_expenses'])): ?>
                        <div class="empty-state">
                            <div class="empty-state__icon">📝</div>
                            <p>No expenses recorded yet.</p>
                            <a href="<?php echo SITE_URL; ?>/dashboard/add-expense.php" class="btn btn--secondary btn--sm">
                                Add Your First Expense
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="transaction-list">
                            <?php foreach ($summary['recent_expenses'] as $expense): ?>
                                <div class="transaction-item">
                                    <div class="transaction-item__icon" style="background-color: <?php echo htmlspecialchars($expense['category_color']); ?>">
                                        <?php echo $expense['category_icon']; ?>
                                    </div>
                                    <div class="transaction-item__details">
                                        <h4 class="transaction-item__title"><?php echo htmlspecialchars($expense['description']); ?></h4>
                                        <p class="transaction-item__meta">
                                            <?php echo htmlspecialchars($expense['category_name']); ?> • 
                                            <?php echo formatDate($expense['expense_date']); ?>
                                        </p>
                                    </div>
                                    <div class="transaction-item__amount">
                                        -<?php echo formatCurrency($expense['amount']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
                
                <!-- Spending by Category -->
                <section class="dashboard-card" aria-labelledby="category-title">
                    <div class="dashboard-card__header">
                        <h2 id="category-title">Spending by Category</h2>
                    </div>
                    
                    <?php 
                    $activeCategories = array_filter($summary['by_category'], function($cat) {
                        return $cat['total'] > 0;
                    });
                    ?>
                    
                    <?php if (empty($activeCategories)): ?>
                        <div class="empty-state">
                            <div class="empty-state__icon">📊</div>
                            <p>No category data available yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="category-list">
                            <?php foreach ($activeCategories as $category): ?>
                                <?php 
                                $percentage = $summary['total_spending'] > 0 
                                    ? ($category['total'] / $summary['total_spending']) * 100 
                                    : 0;
                                ?>
                                <div class="category-item">
                                    <div class="category-item__header">
                                        <span class="category-item__icon"><?php echo $category['category_icon']; ?></span>
                                        <span class="category-item__name"><?php echo htmlspecialchars($category['category_name']); ?></span>
                                        <span class="category-item__amount"><?php echo formatCurrency($category['total']); ?></span>
                                    </div>
                                    <div class="category-item__bar">
                                        <div class="category-item__progress" 
                                             style="width: <?php echo $percentage; ?>%; background-color: <?php echo htmlspecialchars($category['category_color']); ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
            
            <!-- Quick Actions -->
            <section class="quick-actions" aria-labelledby="actions-title">
                <h2 id="actions-title" class="sr-only">Quick Actions</h2>
                <div class="quick-actions__grid">
                    <a href="<?php echo SITE_URL; ?>/dashboard/add-expense.php" class="quick-action">
                        <span class="quick-action__icon">➕</span>
                        <span class="quick-action__label">Add Expense</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/dashboard/reports.php" class="quick-action">
                        <span class="quick-action__icon">📊</span>
                        <span class="quick-action__label">View Reports</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/dashboard/budgets.php" class="quick-action">
                        <span class="quick-action__icon">💵</span>
                        <span class="quick-action__label">Set Budgets</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/dashboard/profile.php" class="quick-action">
                        <span class="quick-action__icon">⚙️</span>
                        <span class="quick-action__label">Settings</span>
                    </a>
                </div>
            </section>
        </div>
    </main>
    
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date('Y'); ?> Smart Expense Tracker | Student ID: 20034038</p>
    </footer>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
