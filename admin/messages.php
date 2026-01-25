<?php
/**
 * Smart Expense Tracking System - Contact Messages Management
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Admin page for viewing and managing contact form submissions
 */

$pageTitle = 'Contact Messages | Admin Panel';
$pageDescription = 'View and manage contact form submissions.';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Require admin or member role
requireRole(['admin', 'member']);

$userId = $_SESSION['user_id'];
$user = getCurrentUser();
$pdo = getDBConnection();

// Handle message actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $messageId = intval($_POST['message_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        
        if ($messageId > 0) {
            switch ($action) {
                case 'mark_read':
                    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE message_id = ?");
                    $stmt->execute([$messageId]);
                    setFlashMessage('success', 'Message marked as read.');
                    break;
                    
                case 'mark_replied':
                    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'replied' WHERE message_id = ?");
                    $stmt->execute([$messageId]);
                    setFlashMessage('success', 'Message marked as replied.');
                    break;
                    
                case 'archive':
                    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'archived' WHERE message_id = ?");
                    $stmt->execute([$messageId]);
                    setFlashMessage('success', 'Message archived.');
                    break;
                    
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE message_id = ?");
                    $stmt->execute([$messageId]);
                    setFlashMessage('success', 'Message deleted.');
                    break;
            }
        }
    }
    redirect(SITE_URL . '/admin/messages.php');
}

// Get messages with filtering
$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if ($statusFilter) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

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
            <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
                <span class="logo__icon admin-logo-icon" aria-hidden="true">⚙️</span>
                <span>Admin Panel</span>
            </a>
            
            <nav class="dashboard-nav" role="navigation" aria-label="Admin navigation">
                <a href="<?php echo SITE_URL; ?>/admin/" class="dashboard-nav__link">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>/admin/users.php" class="dashboard-nav__link">Users</a>
                <a href="<?php echo SITE_URL; ?>/admin/messages.php" class="dashboard-nav__link active">Messages</a>
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
                <h1>Contact Messages</h1>
                <span class="page-header__count"><?php echo count($messages); ?> messages</span>
            </div>
            
            <!-- Filters -->
            <div class="filter-card">
                <form method="GET" action="" class="filter-form">
                    <div class="filter-form__group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="new" <?php echo ($statusFilter === 'new') ? 'selected' : ''; ?>>New</option>
                            <option value="read" <?php echo ($statusFilter === 'read') ? 'selected' : ''; ?>>Read</option>
                            <option value="replied" <?php echo ($statusFilter === 'replied') ? 'selected' : ''; ?>>Replied</option>
                            <option value="archived" <?php echo ($statusFilter === 'archived') ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    
                    <div class="filter-form__actions">
                        <button type="submit" class="btn btn--primary btn--sm">Filter</button>
                        <a href="<?php echo SITE_URL; ?>/admin/messages.php" class="btn btn--secondary btn--sm">Clear</a>
                    </div>
                </form>
            </div>
            
            <!-- Messages List -->
            <?php if (empty($messages)): ?>
                <div class="empty-state">
                    <div class="empty-state__icon">📧</div>
                    <h3>No Messages Found</h3>
                    <p>Contact form submissions will appear here.</p>
                </div>
            <?php else: ?>
                <div class="messages-list">
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-card message-card--<?php echo $msg['status']; ?>">
                            <div class="message-card__header">
                                <div class="message-card__sender">
                                    <strong><?php echo htmlspecialchars($msg['name']); ?></strong>
                                    <span class="message-card__email">&lt;<?php echo htmlspecialchars($msg['email']); ?>&gt;</span>
                                </div>
                                <div class="message-card__meta">
                                    <span class="status-badge status-badge--<?php echo $msg['status']; ?>">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                    <span class="message-card__date"><?php echo formatDate($msg['created_at'], 'd M Y H:i'); ?></span>
                                </div>
                            </div>
                            
                            <div class="message-card__subject">
                                <strong>Subject:</strong> <?php echo htmlspecialchars($msg['subject']); ?>
                            </div>
                            
                            <div class="message-card__body">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            </div>
                            
                            <?php if ($msg['phone']): ?>
                                <div class="message-card__phone">
                                    <strong>Phone:</strong> <?php echo htmlspecialchars($msg['phone']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="message-card__actions">
                                <?php if ($msg['status'] === 'new'): ?>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="message_id" value="<?php echo $msg['message_id']; ?>">
                                        <input type="hidden" name="action" value="mark_read">
                                        <button type="submit" class="btn btn--secondary btn--sm">Mark as Read</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($msg['status'] !== 'replied'): ?>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="message_id" value="<?php echo $msg['message_id']; ?>">
                                        <input type="hidden" name="action" value="mark_replied">
                                        <button type="submit" class="btn btn--primary btn--sm">Mark as Replied</button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" 
                                   class="btn btn--secondary btn--sm">Reply by Email</a>
                                
                                <?php if ($msg['status'] !== 'archived'): ?>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="message_id" value="<?php echo $msg['message_id']; ?>">
                                        <input type="hidden" name="action" value="archive">
                                        <button type="submit" class="btn btn--secondary btn--sm">Archive</button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" class="inline-form" 
                                      onsubmit="return confirm('Are you sure you want to delete this message?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['message_id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn--danger btn--sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date('Y'); ?> Smart Expense Tracker | Admin Panel | Student ID: 20034038</p>
    </footer>
    
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
