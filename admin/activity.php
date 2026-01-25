<?php
/**
 * Smart Expense Tracking System - Activity Log
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Admin page for viewing system activity logs
 * Implements Role-Based Access Control (RBAC)
 */

$pageTitle = 'Activity Log | Admin | Smart Expense Tracker';
$pageDescription = 'System activity log for administrators.';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Require admin or member role
requireRole(['admin', 'member']);

$userId = $_SESSION['user_id'];
$user = getCurrentUser();
$pdo = getDBConnection();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filter by action type
$actionFilter = isset($_GET['action']) ? sanitize($_GET['action']) : '';
$userFilter = isset($_GET['user']) ? sanitize($_GET['user']) : '';

// Build query
$whereClause = "1=1";
$params = [];

if ($actionFilter) {
    $whereClause .= " AND al.action = :action";
    $params['action'] = $actionFilter;
}

if ($userFilter) {
    $whereClause .= " AND (u.username LIKE :user OR u.email LIKE :user)";
    $params['user'] = "%$userFilter%";
}

// Get total count
$countSql = "SELECT COUNT(*) as count FROM activity_log al LEFT JOIN users u ON al.user_id = u.user_id WHERE $whereClause";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalCount = $stmt->fetch()['count'];
$totalPages = ceil($totalCount / $perPage);

// Get activity logs
$sql = "
    SELECT al.*, u.username, u.email, u.first_name, u.last_name
    FROM activity_log al 
    LEFT JOIN users u ON al.user_id = u.user_id 
    WHERE $whereClause
    ORDER BY al.created_at DESC 
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll();

// Get distinct action types for filter
$stmt = $pdo->query("SELECT DISTINCT action FROM activity_log ORDER BY action");
$actionTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get new messages count for badge
$stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'");
$newMessages = $stmt->fetch()['count'];
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
                <a href="<?php echo SITE_URL; ?>/admin/" class="dashboard-nav__link">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>/admin/users.php" class="dashboard-nav__link">Users</a>
                <a href="<?php echo SITE_URL; ?>/admin/messages.php" class="dashboard-nav__link">
                    Messages
                    <?php if ($newMessages > 0): ?>
                        <span class="badge"><?php echo $newMessages; ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/activity.php" class="dashboard-nav__link active">Activity Log</a>
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
            
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Activity Log</h1>
                    <p class="page-header__count"><?php echo number_format($totalCount); ?> total activities</p>
                </div>
            </div>
            
            <!-- Filter Form -->
            <div class="filter-card">
                <form method="GET" class="filter-form">
                    <div class="filter-form__group">
                        <label class="form-label">Action Type</label>
                        <select name="action" class="form-select">
                            <option value="">All Actions</option>
                            <?php foreach ($actionTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $actionFilter === $type ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-form__group">
                        <label class="form-label">User</label>
                        <input type="text" name="user" class="form-input" placeholder="Username or email..." value="<?php echo htmlspecialchars($userFilter); ?>">
                    </div>
                    <div class="filter-form__actions">
                        <button type="submit" class="btn btn--primary btn--sm">Filter</button>
                        <a href="<?php echo SITE_URL; ?>/admin/activity.php" class="btn btn--secondary btn--sm">Reset</a>
                    </div>
                </form>
            </div>
            
            <!-- Activity List -->
            <div class="dashboard-card">
                <?php if (empty($activities)): ?>
                    <div class="empty-state">
                        <div class="empty-state__icon">📋</div>
                        <h3>No Activity Found</h3>
                        <p>No activity logs match your filter criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activities as $activity): ?>
                                    <tr>
                                        <td data-label="Date/Time">
                                            <?php echo formatDate($activity['created_at'], 'd M Y H:i:s'); ?>
                                        </td>
                                        <td data-label="User">
                                            <?php if ($activity['username']): ?>
                                                <strong><?php echo htmlspecialchars($activity['username']); ?></strong>
                                                <br><small><?php echo htmlspecialchars($activity['email']); ?></small>
                                            <?php else: ?>
                                                <span style="color: var(--medium-gray);">Guest</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Action">
                                            <?php
                                            $actionIcons = [
                                                'LOGIN' => '🔐',
                                                'LOGOUT' => '🚪',
                                                'REGISTRATION' => '📝',
                                                'ADD_EXPENSE' => '➕',
                                                'EDIT_EXPENSE' => '✏️',
                                                'DELETE_EXPENSE' => '🗑️',
                                                'FAILED_LOGIN' => '⚠️',
                                                'PROFILE_UPDATE' => '👤',
                                                'PASSWORD_CHANGE' => '🔑'
                                            ];
                                            $icon = $actionIcons[$activity['action']] ?? '📋';
                                            ?>
                                            <span class="category-badge" style="background: var(--light-gray);">
                                                <?php echo $icon; ?> <?php echo htmlspecialchars($activity['action']); ?>
                                            </span>
                                        </td>
                                        <td data-label="Description">
                                            <?php echo htmlspecialchars($activity['description'] ?? '-'); ?>
                                        </td>
                                        <td data-label="IP Address">
                                            <?php echo htmlspecialchars($activity['ip_address'] ?? '-'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination" style="margin-top: var(--spacing-lg); display: flex; justify-content: center; gap: var(--spacing-sm);">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&action=<?php echo urlencode($actionFilter); ?>&user=<?php echo urlencode($userFilter); ?>" class="btn btn--secondary btn--sm">← Previous</a>
                            <?php endif; ?>
                            
                            <span style="padding: 8px 16px; color: var(--medium-gray);">
                                Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                            </span>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&action=<?php echo urlencode($actionFilter); ?>&user=<?php echo urlencode($userFilter); ?>" class="btn btn--secondary btn--sm">Next →</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date('Y'); ?> Smart Expense Tracker | Admin Panel | Student ID: 20034038</p>
    </footer>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
