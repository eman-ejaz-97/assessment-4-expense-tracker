<?php
/**
 * Smart Expense Tracking System - User Logout
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Handles secure session destruction and user logout
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Destroy session and logout
destroyUserSession();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Set flash message and redirect
session_start();
setFlashMessage('success', 'You have been successfully logged out.');
redirect(SITE_URL . '/auth/login.php');
?>
