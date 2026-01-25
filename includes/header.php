<?php
/**
 * Smart Expense Tracking System - Header Include
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Common header include for all pages
 * Contains navigation, meta tags, and SEO elements
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

// Get current page for active nav highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageDir = basename(dirname($_SERVER['PHP_SELF']));

// Default page variables if not set
$pageTitle = $pageTitle ?? 'Smart Expense Tracker';
$pageDescription = $pageDescription ?? 'Smart Expense Tracking System - Track expenses in real-time, categorize spending automatically, generate reports, and set custom budget alerts.';
$pageKeywords = $pageKeywords ?? 'expense tracking, budget management, financial technology, expense reports, budget alerts';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords); ?>">
    <meta name="author" content="Smart Expense Tracking System - Student ID: 20034038">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags for Social Sharing -->
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars(SITE_URL . $_SERVER['REQUEST_URI']); ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/images/og-image.png">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    
    <!-- Canonical URL for SEO -->
    <link rel="canonical" href="<?php echo htmlspecialchars(SITE_URL . $_SERVER['REQUEST_URI']); ?>">
    
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/styles.css">
    
    <!-- Structured Data for SEO (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "Smart Expense Tracker",
        "description": "<?php echo htmlspecialchars($pageDescription); ?>",
        "url": "<?php echo SITE_URL; ?>",
        "applicationCategory": "FinanceApplication",
        "operatingSystem": "Any",
        "offers": {
            "@type": "Offer",
            "price": "9.00",
            "priceCurrency": "AUD"
        },
        "author": {
            "@type": "Organization",
            "name": "Smart Expense Tracker Team"
        }
    }
    </script>
</head>
<body>
    <!-- Skip Link for Accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Header -->
    <header class="header" role="banner">
        <div class="header__inner">
            <a href="<?php echo SITE_URL; ?>/index.php" class="logo" aria-label="Smart Expense Tracker Home">
                <span class="logo__icon" aria-hidden="true">$</span>
                <span>ExpenseTracker</span>
            </a>
            
            <button class="menu-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
                <span class="menu-toggle__bar"></span>
                <span class="menu-toggle__bar"></span>
                <span class="menu-toggle__bar"></span>
            </button>
            
            <nav class="nav" role="navigation" aria-label="Main navigation">
                <ul class="nav__list">
                    <li><a href="<?php echo SITE_URL; ?>/index.php" class="nav__link <?php echo ($currentPage === 'index' && $pageDir !== 'dashboard' && $pageDir !== 'admin') ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php" class="nav__link <?php echo ($currentPage === 'about') ? 'active' : ''; ?>">About</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/features.php" class="nav__link <?php echo ($currentPage === 'features') ? 'active' : ''; ?>">Features</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pricing.php" class="nav__link <?php echo ($currentPage === 'pricing') ? 'active' : ''; ?>">Pricing</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/gallery.php" class="nav__link <?php echo ($currentPage === 'gallery') ? 'active' : ''; ?>">Gallery</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php" class="nav__link <?php echo ($currentPage === 'contact') ? 'active' : ''; ?>">Contact</a></li>
                    
                    <?php if (isLoggedIn()): ?>
                        <!-- Logged in user menu -->
                        <li class="nav__dropdown">
                            <a href="<?php echo SITE_URL; ?>/dashboard/" class="nav__link nav__link--user <?php echo ($pageDir === 'dashboard') ? 'active' : ''; ?>">
                                <span class="nav__user-icon" aria-hidden="true">👤</span>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <div class="nav__dropdown-menu">
                                <a href="<?php echo SITE_URL; ?>/dashboard/" class="nav__dropdown-link">Dashboard</a>
                                <a href="<?php echo SITE_URL; ?>/dashboard/expenses.php" class="nav__dropdown-link">My Expenses</a>
                                <a href="<?php echo SITE_URL; ?>/dashboard/profile.php" class="nav__dropdown-link">Profile</a>
                                <?php if (hasRole(['admin', 'member'])): ?>
                                    <a href="<?php echo SITE_URL; ?>/admin/" class="nav__dropdown-link">Admin Panel</a>
                                <?php endif; ?>
                                <hr class="nav__dropdown-divider">
                                <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="nav__dropdown-link nav__dropdown-link--danger">Logout</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <!-- Guest menu -->
                        <li><a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn--secondary btn--sm">Login</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn--primary btn--sm">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- Flash Messages -->
    <?php echo displayFlashMessage(); ?>
    
    <!-- Main Content -->
    <main id="main-content">
