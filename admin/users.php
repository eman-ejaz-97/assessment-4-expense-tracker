<?php
/**
 * Smart Expense Tracking System - User Management
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Admin page for viewing and managing users
 * Implements Role-Based Access Control for user management
 */

$pageTitle = 'Manage Users | Admin Panel';
$pageDescription = 'Admin user management page.';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Require admin role only (members can view but not modify)
requireRole(['admin', 'member']);

$userId = $_SESSION['user_id'];
$user = getCurrentUser();
$pdo = getDBConnection();
$isAdmin = hasRole('admin');

// Handle user actions (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $targetUserId = intval($_POST['user_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        
        // Prevent self-modification
        if ($targetUserId > 0 && $targetUserId !== $userId) {
            switch ($action) {
                case 'change_role':
                    $newRole = $_POST['new_role'] ?? '';
                    if (in_array($newRole, ['admin', 'member', 'user'])) {
                        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
                        $stmt->execute([$newRole, $targetUserId]);
                        logActivity('ADMIN_CHANGE_ROLE', "Changed user $targetUserId role to $newRole");
                        setFlashMessage('success', 'User role updated successfully.');
                    }
                    break;
                    
                case 'change_status':
                    $newStatus = $_POST['new_status'] ?? '';
                    if (in_array($newStatus, ['active', 'inactive', 'suspended'])) {
                        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
                        $stmt->execute([$newStatus, $targetUserId]);
                        logActivity('ADMIN_CHANGE_STATUS', "Changed user $targetUserId status to $newStatus");
                        setFlashMessage('success', 'User status updated successfully.');
                    }
                    break;
                    
                case 'delete':
                    // Soft delete or actual delete
                    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role != 'admin'");
                    $stmt->execute([$targetUserId]);
                    logActivity('ADMIN_DELETE_USER', "Deleted user $targetUserId");
                    setFlashMessage('success', 'User deleted successfully.');
                    break;
            }
        }
    }
    redirect(SITE_URL . '/admin/users.php');
}

// Get all users with filtering
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if ($roleFilter) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}

if ($statusFilter) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

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
<body class="dashboard-body admin-body">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <header class="dashboard-header admin-header" role="banner">
        <div class="dashboard-header__inner">
            <a href="<?php echo SITE_URL; ?>/index.php" class="logo" aria-label="Smart Expense Tracker Home">
                <span class="logo__icon admin-logo-icon" aria-hidden="true">⚙️</span>
                <span>Admin Panel</span>
            </a>
            
            <nav class="dashboard-nav" role="navigation" aria-label="Admin navigation">
                <a href="<?php echo SITE_URL; ?>/admin/" class="dashboard-nav__link">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>/admin/users.php" class="dashboard-nav__link active">Users</a>
                <a href="<?php echo SITE_URL; ?>/admin/messages.php" class="dashboard-nav__link">Messages</a>
                <a href="<?php echo SITE_URL; ?>/admin/activity.php" class="dashboard-nav__link">Activity Log</a>
                <a href="<?php echo SITE_URL; ?>/dashboard/" class="dashboard-nav__link">← User Dashboard</a>
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
                <h1>Manage Users</h1>
                <span class="page-header__count"><?php echo count($users); ?> users found</span>
            </div>
            
            <!-- Filters -->
            <div class="filter-card">
                <form method="GET" action="" class="filter-form">
                    <div class="filter-form__group">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" id="search" name="search" class="form-input"
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Username, email, or name...">
                    </div>
                    
                    <div class="filter-form__group">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" name="role" class="form-select">
                            <option value="">All Roles</option>
                            <option value="admin" <?php echo ($roleFilter === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="member" <?php echo ($roleFilter === 'member') ? 'selected' : ''; ?>>Member</option>
                            <option value="user" <?php echo ($roleFilter === 'user') ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                    
                    <div class="filter-form__group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active" <?php echo ($statusFilter === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($statusFilter === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="suspended" <?php echo ($statusFilter === 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                    
                    <div class="filter-form__actions">
                        <button type="submit" class="btn btn--primary btn--sm">Filter</button>
                        <a href="<?php echo SITE_URL; ?>/admin/users.php" class="btn btn--secondary btn--sm">Clear</a>
                    </div>
                </form>
            </div>
            
            <!-- Users Table -->
            <div class="table-responsive">
                <table class="data-table" role="table">
                    <caption class="sr-only">List of registered users</caption>
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">User</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                            <th scope="col">Last Login</th>
                            <th scope="col">Registered</th>
                            <?php if ($isAdmin): ?>
                                <th scope="col">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td data-label="ID"><?php echo $u['user_id']; ?></td>
                                <td data-label="User">
                                    <strong><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></strong><br>
                                    <small>@<?php echo htmlspecialchars($u['username']); ?></small><br>
                                    <small><?php echo htmlspecialchars($u['email']); ?></small>
                                </td>
                                <td data-label="Role">
                                    <span class="role-badge role-badge--<?php echo $u['role']; ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span class="status-badge status-badge--<?php echo $u['status']; ?>">
                                        <?php echo ucfirst($u['status']); ?>
                                    </span>
                                </td>
                                <td data-label="Last Login">
                                    <?php echo $u['last_login'] ? formatDate($u['last_login'], 'd M Y H:i') : 'Never'; ?>
                                </td>
                                <td data-label="Registered">
                                    <?php echo formatDate($u['created_at']); ?>
                                </td>
                                <?php if ($isAdmin): ?>
                                    <td data-label="Actions" class="actions-cell">
                                        <?php if ($u['user_id'] !== $userId): ?>
                                            <!-- Change Role -->
                                            <form method="POST" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                                <input type="hidden" name="action" value="change_role">
                                                <select name="new_role" class="form-select form-select--sm" 
                                                        onchange="this.form.submit()">
                                                    <option value="">Change Role</option>
                                                    <option value="admin">Admin</option>
                                                    <option value="member">Member</option>
                                                    <option value="user">User</option>
                                                </select>
                                            </form>
                                            
                                            <!-- Change Status -->
                                            <form method="POST" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                                <input type="hidden" name="action" value="change_status">
                                                <select name="new_status" class="form-select form-select--sm"
                                                        onchange="this.form.submit()">
                                                    <option value="">Change Status</option>
                                                    <option value="active">Active</option>
                                                    <option value="inactive">Inactive</option>
                                                    <option value="suspended">Suspended</option>
                                                </select>
                                            </form>
                                            
                                            <?php if ($u['role'] !== 'admin'): ?>
                                                <!-- Delete -->
                                                <form method="POST" class="inline-form"
                                                      onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="btn btn--danger btn--sm">🗑️</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <em>Current user</em>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date('Y'); ?> Smart Expense Tracker | Admin Panel | Student ID: 20034038</p>
    </footer>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
