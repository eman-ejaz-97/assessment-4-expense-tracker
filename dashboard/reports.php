<?php
/**
 * Smart Expense Tracking System - Reports Page
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Displays expense reports and analytics
 */

$pageTitle = 'Reports | Smart Expense Tracker';
$pageDescription = 'View your expense reports and analytics.';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Require authentication
requireLogin();

$userId = $_SESSION['user_id'];
$user = getCurrentUser();
$pdo = getDBConnection();

// Get report parameters
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Validate month and year
if ($month < 1 || $month > 12) $month = date('n');
if ($year < 2020 || $year > date('Y') + 1) $year = date('Y');

// Get monthly summary
$stmt = $pdo->prepare("
    SELECT 
        c.category_name,
        c.category_icon,
        c.category_color,
        COUNT(e.expense_id) as transaction_count,
        COALESCE(SUM(e.amount), 0) as total_amount
    FROM expense_categories c
    LEFT JOIN expenses e ON c.category_id = e.category_id 
        AND e.user_id = ? 
        AND EXTRACT(MONTH FROM e.expense_date) = ? 
        AND EXTRACT(YEAR FROM e.expense_date) = ?
    WHERE c.is_default = TRUE OR c.user_id = ?
    GROUP BY c.category_id, c.category_name, c.category_icon, c.category_color
    ORDER BY total_amount DESC
");
$stmt->execute([$userId, $month, $year, $userId]);
$categoryData = $stmt->fetchAll();

// Get total for month
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM expenses 
    WHERE user_id = ? AND EXTRACT(MONTH FROM expense_date) = ? AND EXTRACT(YEAR FROM expense_date) = ?
");
$stmt->execute([$userId, $month, $year]);
$monthlyTotal = $stmt->fetch()['total'];

// Get daily breakdown
$stmt = $pdo->prepare("
    SELECT 
        EXTRACT(DAY FROM expense_date)::INTEGER as day,
        SUM(amount) as daily_total
    FROM expenses 
    WHERE user_id = ? AND EXTRACT(MONTH FROM expense_date) = ? AND EXTRACT(YEAR FROM expense_date) = ?
    GROUP BY EXTRACT(DAY FROM expense_date)
    ORDER BY day
");
$stmt->execute([$userId, $month, $year]);
$dailyData = $stmt->fetchAll();

// Get payment method breakdown
$stmt = $pdo->prepare("
    SELECT 
        payment_method,
        COUNT(*) as count,
        SUM(amount) as total
    FROM expenses 
    WHERE user_id = ? AND EXTRACT(MONTH FROM expense_date) = ? AND EXTRACT(YEAR FROM expense_date) = ?
    GROUP BY payment_method
    ORDER BY total DESC
");
$stmt->execute([$userId, $month, $year]);
$paymentData = $stmt->fetchAll();

// Get last 6 months trend
$stmt = $pdo->prepare("
    SELECT 
        EXTRACT(MONTH FROM expense_date)::INTEGER as month,
        EXTRACT(YEAR FROM expense_date)::INTEGER as year,
        SUM(amount) as total
    FROM expenses 
    WHERE user_id = ? AND expense_date >= CURRENT_DATE - INTERVAL '6 months'
    GROUP BY EXTRACT(YEAR FROM expense_date), EXTRACT(MONTH FROM expense_date)
    ORDER BY year, month
");
$stmt->execute([$userId]);
$trendData = $stmt->fetchAll();

$monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
               'July', 'August', 'September', 'October', 'November', 'December'];
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
                <a href="<?php echo SITE_URL; ?>/dashboard/reports.php" class="dashboard-nav__link active">Reports</a>
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
                <h1>Expense Reports</h1>
            </div>
            
            <!-- Month/Year Selector -->
            <div class="filter-card">
                <form method="GET" action="" class="filter-form">
                    <div class="filter-form__group">
                        <label for="month" class="form-label">Month</label>
                        <select id="month" name="month" class="form-select">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo ($month === $m) ? 'selected' : ''; ?>>
                                    <?php echo $monthNames[$m - 1]; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="filter-form__group">
                        <label for="year" class="form-label">Year</label>
                        <select id="year" name="year" class="form-select">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($year === $y) ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="filter-form__actions">
                        <button type="submit" class="btn btn--primary btn--sm">View Report</button>
                    </div>
                </form>
            </div>
            
            <!-- Monthly Summary -->
            <section class="dashboard-welcome" style="margin-bottom: var(--spacing-lg);">
                <div class="dashboard-welcome__content">
                    <h2 style="color: white; margin-bottom: var(--spacing-sm);">
                        <?php echo $monthNames[$month - 1] . ' ' . $year; ?> Summary
                    </h2>
                    <p style="font-size: 2rem; font-weight: 700; margin: 0;">
                        <?php echo formatCurrency($monthlyTotal); ?>
                    </p>
                    <p style="opacity: 0.9; margin: 0;">Total Expenses</p>
                </div>
            </section>
            
            <div class="dashboard-grid">
                <!-- Category Breakdown -->
                <section class="dashboard-card" aria-labelledby="category-report">
                    <div class="dashboard-card__header">
                        <h2 id="category-report">Spending by Category</h2>
                    </div>
                    
                    <?php if ($monthlyTotal > 0): ?>
                        <div class="category-list">
                            <?php foreach ($categoryData as $cat): ?>
                                <?php if ($cat['total_amount'] > 0): ?>
                                    <?php $percentage = ($cat['total_amount'] / $monthlyTotal) * 100; ?>
                                    <div class="category-item">
                                        <div class="category-item__header">
                                            <span class="category-item__icon"><?php echo $cat['category_icon']; ?></span>
                                            <span class="category-item__name"><?php echo htmlspecialchars($cat['category_name']); ?></span>
                                            <span class="category-item__amount">
                                                <?php echo formatCurrency($cat['total_amount']); ?>
                                                <small style="color: var(--medium-gray);">(<?php echo number_format($percentage, 1); ?>%)</small>
                                            </span>
                                        </div>
                                        <div class="category-item__bar">
                                            <div class="category-item__progress" 
                                                 style="width: <?php echo $percentage; ?>%; background-color: <?php echo htmlspecialchars($cat['category_color']); ?>">
                                            </div>
                                        </div>
                                        <small style="color: var(--medium-gray);">
                                            <?php echo $cat['transaction_count']; ?> transaction(s)
                                        </small>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state__icon">📊</div>
                            <p>No expenses recorded for this month.</p>
                        </div>
                    <?php endif; ?>
                </section>
                
                <!-- Payment Method Breakdown -->
                <section class="dashboard-card" aria-labelledby="payment-report">
                    <div class="dashboard-card__header">
                        <h2 id="payment-report">By Payment Method</h2>
                    </div>
                    
                    <?php if (!empty($paymentData)): ?>
                        <div class="category-list">
                            <?php 
                            $paymentIcons = [
                                'cash' => '💵',
                                'credit_card' => '💳',
                                'debit_card' => '💳',
                                'bank_transfer' => '🏦',
                                'paypal' => '📱',
                                'other' => '📦'
                            ];
                            foreach ($paymentData as $payment): 
                                $percentage = ($payment['total'] / $monthlyTotal) * 100;
                            ?>
                                <div class="category-item">
                                    <div class="category-item__header">
                                        <span class="category-item__icon">
                                            <?php echo $paymentIcons[$payment['payment_method']] ?? '📦'; ?>
                                        </span>
                                        <span class="category-item__name">
                                            <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                        </span>
                                        <span class="category-item__amount">
                                            <?php echo formatCurrency($payment['total']); ?>
                                        </span>
                                    </div>
                                    <div class="category-item__bar">
                                        <div class="category-item__progress" 
                                             style="width: <?php echo $percentage; ?>%; background-color: var(--primary-color);">
                                        </div>
                                    </div>
                                    <small style="color: var(--medium-gray);">
                                        <?php echo $payment['count']; ?> transaction(s)
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state__icon">💳</div>
                            <p>No payment data available.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
            
            <!-- 6-Month Trend -->
            <section class="dashboard-card" style="margin-top: var(--spacing-lg);" aria-labelledby="trend-report">
                <div class="dashboard-card__header">
                    <h2 id="trend-report">6-Month Spending Trend</h2>
                </div>
                
                <?php if (!empty($trendData)): ?>
                    <div class="trend-chart">
                        <?php 
                        $maxAmount = max(array_column($trendData, 'total'));
                        ?>
                        <div class="trend-bars">
                            <?php foreach ($trendData as $trend): ?>
                                <?php $height = $maxAmount > 0 ? ($trend['total'] / $maxAmount) * 100 : 0; ?>
                                <div class="trend-bar-container">
                                    <div class="trend-bar" style="height: <?php echo $height; ?>%;">
                                        <span class="trend-bar__value"><?php echo formatCurrency($trend['total']); ?></span>
                                    </div>
                                    <span class="trend-bar__label">
                                        <?php echo substr($monthNames[$trend['month'] - 1], 0, 3); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state__icon">📈</div>
                        <p>No trend data available yet.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
    
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date('Y'); ?> Smart Expense Tracker | Student ID: 20034038</p>
    </footer>
    
    <style>
        .trend-chart {
            padding: var(--spacing-lg) 0;
        }
        .trend-bars {
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            height: 200px;
            padding: var(--spacing-md);
            background: var(--light-gray);
            border-radius: var(--radius-md);
        }
        .trend-bar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }
        .trend-bar {
            width: 40px;
            background: linear-gradient(to top, var(--primary-color), var(--primary-light));
            border-radius: var(--radius-sm) var(--radius-sm) 0 0;
            position: relative;
            min-height: 20px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }
        .trend-bar__value {
            position: absolute;
            top: -25px;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--dark-gray);
            white-space: nowrap;
        }
        .trend-bar__label {
            margin-top: var(--spacing-sm);
            font-size: 0.8rem;
            color: var(--medium-gray);
        }
    </style>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
