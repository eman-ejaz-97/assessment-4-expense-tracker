<?php
/**
 * Smart Expense Tracking System - Admin Dashboard
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Admin panel for managing users, viewing system stats,
 * and accessing administrative features
 * Implements Role-Based Access Control (RBAC)
 */

$pageTitle = 'Admin Dashboard | Smart Expense Tracker';
$pageDescription = 'Admin dashboard for system management.';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Require admin or member role
requireRole(['admin', 'member']);

$userId = $_SESSION['user_id'];
$user = getCurrentUser();
$pdo = getDBConnection();

// Get system statistics
$stats = [];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $stmt->fetch()['count'];

// Active users (logged in within 30 days)
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE last_login >= NOW() - INTERVAL '30 days'");
$stats['active_users'] = $stmt->fetch()['count'];

// Total expenses
$stmt = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM expenses");
$expenseStats = $stmt->fetch();
$stats['total_expenses'] = $expenseStats['count'];
$stats['total_amount'] = $expenseStats['total'];

// Contact messages (new)
$stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'");
$stats['new_messages'] = $stmt->fetch()['count'];

// Get recent users
$stmt = $pdo->query("
    SELECT user_id, username, email, first_name, last_name, role, status, created_at, last_login 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recentUsers = $stmt->fetchAll();

// Get recent activity
$stmt = $pdo->query("
    SELECT al.*, u.username 
    FROM activity_log al 
    LEFT JOIN users u ON al.user_id = u.user_id 
    ORDER BY al.created_at DESC 
    LIMIT 10
");
$recentActivity = $stmt->fetchAll();
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
<body class="dashboard-body admin-body">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Admin Header -->
    <header class="dashboard-header admin-header" role="banner">
        <div class="dashboard-header__inner">
            <a href="<?php echo SITE_URL; ?>/index.php" class="logo" aria-label="Smart Expense Tracker Home">
                <span class="logo__icon admin-logo-icon" aria-hidden="true">⚙️</span>
                <span>Admin Panel</span>
            </a>
            
            <nav class="dashboard-nav" role="navigation" aria-label="Admin navigation">
                <a href="<?php echo SITE_URL; ?>/admin/" class="dashboard-nav__link active">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>/admin/users.php" class="dashboard-nav__link">Users</a>
                <a href="<?php echo SITE_URL; ?>/admin/messages.php" class="dashboard-nav__link">
                    Messages
                    <?php if ($stats['new_messages'] > 0): ?>
                        <span class="badge"><?php echo $stats['new_messages']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/activity.php" class="dashboard-nav__link">Activity Log</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/" class="dashboard-nav__link">← User Dashboard</a>
            </nav>
            
            <div class="dashboard-user">
                <span class="dashboard-user__role"><?php echo ucfirst($user['role']); ?></span>
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
            
            <!-- Admin Welcome -->
            <section class="dashboard-welcome admin-welcome">
                <div class="dashboard-welcome__content">
                    <h1>Admin Dashboard</h1>
                    <p>System overview and management tools</p>
                </div>
                <div class="admin-welcome__role">
                    <span class="role-badge role-badge--<?php echo $user['role']; ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                </div>
            </section>
            
            <!-- Stats Cards -->
            <section class="dashboard-stats" aria-label="System statistics">
                <div class="stat-card stat-card--admin">
                    <div class="stat-card__icon">👥</div>
                    <div class="stat-card__content">
                        <h3 class="stat-card__value"><?php echo number_format($stats['total_users']); ?></h3>
                        <p class="stat-card__label">Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card stat-card--admin">
                    <div class="stat-card__icon">✅</div>
                    <div class="stat-card__content">
                        <h3 class="stat-card__value"><?php echo number_format($stats['active_users']); ?></h3>
                        <p class="stat-card__label">Active (30 days)</p>
                    </div>
                </div>
                
                <div class="stat-card stat-card--admin">
                    <div class="stat-card__icon">📊</div>
                    <div class="stat-card__content">
                        <h3 class="stat-card__value"><?php echo number_format($stats['total_expenses']); ?></h3>
                        <p class="stat-card__label">Total Expenses</p>
                    </div>
                </div>
                
                <div class="stat-card stat-card--admin">
                    <div class="stat-card__icon">💰</div>
                    <div class="stat-card__content">
                        <h3 class="stat-card__value"><?php echo formatCurrency($stats['total_amount']); ?></h3>
                        <p class="stat-card__label">Total Amount</p>
                    </div>
                </div>
            </section>
            
            <div class="dashboard-grid">
                <!-- Recent Users -->
                <section class="dashboard-card" aria-labelledby="users-title">
                    <div class="dashboard-card__header">
                        <h2 id="users-title">Recent Users</h2>
                        <a href="<?php echo SITE_URL; ?>/admin/users.php" class="dashboard-card__link">View All</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table data-table--compact">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $u): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></strong>
                                            <br><small><?php echo htmlspecialchars($u['email']); ?></small>
                                        </td>
                                        <td>
                                            <span class="role-badge role-badge--<?php echo $u['role']; ?>">
                                                <?php echo ucfirst($u['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-badge--<?php echo $u['status']; ?>">
                                                <?php echo ucfirst($u['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($u['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                
                <!-- Recent Activity -->
                <section class="dashboard-card" aria-labelledby="activity-title">
                    <div class="dashboard-card__header">
                        <h2 id="activity-title">Recent Activity</h2>
                        <a href="<?php echo SITE_URL; ?>/admin/activity.php" class="dashboard-card__link">View All</a>
                    </div>
                    
                    <div class="activity-list">
                        <?php foreach ($recentActivity as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-item__icon">
                                    <?php
                                    $icons = [
                                        'LOGIN' => '🔐',
                                        'LOGOUT' => '🚪',
                                        'REGISTRATION' => '📝',
                                        'ADD_EXPENSE' => '➕',
                                        'EDIT_EXPENSE' => '✏️',
                                        'DELETE_EXPENSE' => '🗑️',
                                        'FAILED_LOGIN' => '⚠️'
                                    ];
                                    echo $icons[$activity['action']] ?? '📋';
                                    ?>
                                </div>
                                <div class="activity-item__content">
                                    <p class="activity-item__action">
                                        <strong><?php echo htmlspecialchars($activity['username'] ?? 'Guest'); ?></strong>
                                        - <?php echo htmlspecialchars($activity['action']); ?>
                                    </p>
                                    <?php if ($activity['description']): ?>
                                        <p class="activity-item__desc"><?php echo htmlspecialchars($activity['description']); ?></p>
                                    <?php endif; ?>
                                    <p class="activity-item__meta">
                                        <?php echo formatDate($activity['created_at'], 'd M Y H:i'); ?>
                                        <?php if ($activity['ip_address']): ?>
                                            • IP: <?php echo htmlspecialchars($activity['ip_address']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
            
            <!-- Quick Admin Actions -->
            <section class="quick-actions" aria-labelledby="admin-actions-title">
                <h2 id="admin-actions-title" class="sr-only">Admin Quick Actions</h2>
                <div class="quick-actions__grid">
                    <a href="<?php echo SITE_URL; ?>/admin/users.php" class="quick-action quick-action--admin">
                        <span class="quick-action__icon">👥</span>
                        <span class="quick-action__label">Manage Users</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/messages.php" class="quick-action quick-action--admin">
                        <span class="quick-action__icon">📧</span>
                        <span class="quick-action__label">View Messages</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/activity.php" class="quick-action quick-action--admin">
                        <span class="quick-action__icon">📋</span>
                        <span class="quick-action__label">Activity Log</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="quick-action quick-action--admin">
                        <span class="quick-action__icon">⚙️</span>
                        <span class="quick-action__label">Settings</span>
                    </a>
                </div>
            </section>
        </div>
    </main>
    
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date('Y'); ?> Smart Expense Tracker | Admin Panel | Student ID: 20034038</p>
    </footer>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
