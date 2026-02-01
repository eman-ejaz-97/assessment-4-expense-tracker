<?php
/**
 * Smart Expense Tracking System - Database Configuration
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * This file contains database connection settings and establishes
 * a secure PDO connection to the PostgreSQL database.
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'expense_tracker');
define('DB_USER', 'postgres');
define('DB_PASS', 'postgres');

// Application configuration
define('APP_NAME', 'Smart Expense Tracker');
define('APP_VERSION', '2.0');
define('SITE_URL', 'http://localhost/20034038_Assessment_4');

// Email configuration (Gmail SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'memanejaz97@gmail.com');
define('SMTP_PASS', 'zrhf ilnw ddio mlys');
define('SMTP_FROM', 'memanejaz97@gmail.com');
define('SMTP_FROM_NAME', 'Smart Expense Tracker');

/**
 * Establishes a secure PDO database connection to PostgreSQL
 * Uses prepared statements to prevent SQL injection
 * 
 * @return PDO Database connection object
 * @throws PDOException If connection fails
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            // Set UTF-8 encoding for PostgreSQL
            $pdo->exec("SET NAMES 'UTF8'");
        } catch (PDOException $e) {
            // Log error and display user-friendly message
            error_log("Database Connection Error: " . $e->getMessage());
            die("Sorry, we're experiencing technical difficulties. Please try again later.");
        }
    }
    
    return $pdo;
}

/**
 * Test database connection
 * 
 * @return bool True if connection successful, false otherwise
 */
function testConnection() {
    try {
        $pdo = getDBConnection();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
