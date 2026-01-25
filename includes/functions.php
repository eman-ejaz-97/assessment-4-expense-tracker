<?php
/**
 * Smart Expense Tracking System - Common Functions
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * This file contains utility functions used throughout the application
 * for validation, sanitization, and common operations.
 */

require_once __DIR__ . '/../database/config.php';

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 * 
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * Must contain: 8+ chars, uppercase, lowercase, number, special char
 * 
 * @param string $password Password to validate
 * @return array [valid: bool, message: string]
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return [
        'valid' => empty($errors),
        'message' => implode('. ', $errors)
    ];
}

/**
 * Hash password securely using bcrypt
 * 
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 * 
 * @param string $password Plain text password
 * @param string $hash Stored hash
 * @return bool True if password matches
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token for form protection
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from form submission
 * 
 * @param string $token Token to verify
 * @return bool True if valid
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Display flash message
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message content
 * @return void
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message or null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message HTML
 * 
 * @return string HTML for flash message
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $typeClass = 'alert--' . $flash['type'];
        return '<div class="alert ' . $typeClass . '" role="alert">' . 
               htmlspecialchars($flash['message']) . 
               '<button type="button" class="alert__close" onclick="this.parentElement.remove()">&times;</button></div>';
    }
    return '';
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 * 
 * @param string|array $roles Role(s) to check
 * @return bool True if user has role
 */
function hasRole($roles) {
    if (!isLoggedIn()) return false;
    
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['user_role'] ?? '', $roles);
}

/**
 * Require user to be logged in
 * Redirects to login if not authenticated
 * 
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please log in to access this page.');
        redirect(SITE_URL . '/auth/login.php');
    }
}

/**
 * Require specific role(s) to access page
 * 
 * @param string|array $roles Required role(s)
 * @return void
 */
function requireRole($roles) {
    requireLogin();
    
    if (!hasRole($roles)) {
        setFlashMessage('error', 'You do not have permission to access this page.');
        redirect(SITE_URL . '/dashboard/index.php');
    }
}

/**
 * Get current user data
 * 
 * @return array|null User data or null
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Log activity for auditing
 * 
 * @param string $action Action performed
 * @param string $description Description of action
 * @param int|null $userId User ID (null for guest actions)
 * @return void
 */
function logActivity($action, $description = null, $userId = null) {
    $pdo = getDBConnection();
    
    if ($userId === null && isLoggedIn()) {
        $userId = $_SESSION['user_id'];
    }
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $description, $ip]);
}

/**
 * Format currency amount
 * 
 * @param float $amount Amount to format
 * @param string $currency Currency code
 * @return string Formatted amount
 */
function formatCurrency($amount, $currency = 'AUD') {
    $symbols = [
        'AUD' => '$',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£'
    ];
    
    $symbol = $symbols[$currency] ?? '$';
    return $symbol . number_format($amount, 2);
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

/**
 * Get user's expenses with optional filters
 * 
 * @param int $userId User ID
 * @param array $filters Optional filters
 * @return array Expenses
 */
function getUserExpenses($userId, $filters = []) {
    $pdo = getDBConnection();
    
    $sql = "SELECT e.*, c.category_name, c.category_icon, c.category_color 
            FROM expenses e 
            JOIN expense_categories c ON e.category_id = c.category_id 
            WHERE e.user_id = ?";
    $params = [$userId];
    
    // Apply date filter
    if (!empty($filters['start_date'])) {
        $sql .= " AND e.expense_date >= ?";
        $params[] = $filters['start_date'];
    }
    if (!empty($filters['end_date'])) {
        $sql .= " AND e.expense_date <= ?";
        $params[] = $filters['end_date'];
    }
    
    // Apply category filter
    if (!empty($filters['category_id'])) {
        $sql .= " AND e.category_id = ?";
        $params[] = $filters['category_id'];
    }
    
    $sql .= " ORDER BY e.expense_date DESC, e.created_at DESC";
    
    // Apply limit
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT " . intval($filters['limit']);
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get expense categories (default + user custom)
 * 
 * @param int|null $userId User ID for custom categories
 * @return array Categories
 */
function getCategories($userId = null) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM expense_categories WHERE is_default = TRUE";
    $params = [];
    
    if ($userId) {
        $sql .= " OR user_id = ?";
        $params[] = $userId;
    }
    
    $sql .= " ORDER BY is_default DESC, category_name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get spending summary for dashboard
 * 
 * @param int $userId User ID
 * @param int $month Month (1-12)
 * @param int $year Year
 * @return array Summary data
 */
function getSpendingSummary($userId, $month = null, $year = null) {
    $pdo = getDBConnection();
    
    if ($month === null) $month = date('n');
    if ($year === null) $year = date('Y');
    
    // Total spending this month
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM expenses 
        WHERE user_id = ? AND EXTRACT(MONTH FROM expense_date) = ? AND EXTRACT(YEAR FROM expense_date) = ?
    ");
    $stmt->execute([$userId, $month, $year]);
    $totalSpending = $stmt->fetch()['total'];
    
    // Spending by category
    $stmt = $pdo->prepare("
        SELECT c.category_name, c.category_icon, c.category_color, 
               COALESCE(SUM(e.amount), 0) as total
        FROM expense_categories c
        LEFT JOIN expenses e ON c.category_id = e.category_id 
            AND e.user_id = ? AND EXTRACT(MONTH FROM e.expense_date) = ? AND EXTRACT(YEAR FROM e.expense_date) = ?
        WHERE c.is_default = TRUE OR c.user_id = ?
        GROUP BY c.category_id, c.category_name, c.category_icon, c.category_color
        ORDER BY total DESC
    ");
    $stmt->execute([$userId, $month, $year, $userId]);
    $byCategory = $stmt->fetchAll();
    
    // Recent transactions
    $recentExpenses = getUserExpenses($userId, ['limit' => 5]);
    
    return [
        'total_spending' => $totalSpending,
        'by_category' => $byCategory,
        'recent_expenses' => $recentExpenses,
        'month' => $month,
        'year' => $year
    ];
}

/**
 * Validate phone number format
 * 
 * @param string $phone Phone number
 * @return bool True if valid
 */
function isValidPhone($phone) {
    // Allow various phone formats
    $pattern = '/^[\d\s\-\+\(\)]+$/';
    return preg_match($pattern, $phone) && strlen(preg_replace('/\D/', '', $phone)) >= 8;
}

/**
 * Generate pagination HTML
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $baseUrl Base URL for pagination links
 * @return string Pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) return '';
    
    $html = '<nav class="pagination" aria-label="Pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="pagination__link">&laquo; Previous</a>';
    }
    
    // Page numbers
    for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
        $activeClass = ($i === $currentPage) ? 'pagination__link--active' : '';
        $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination__link ' . $activeClass . '">' . $i . '</a>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="pagination__link">Next &raquo;</a>';
    }
    
    $html .= '</nav>';
    return $html;
}
?>
