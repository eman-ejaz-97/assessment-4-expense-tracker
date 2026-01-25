<?php
/**
 * Smart Expense Tracking System - Features Page
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 */

$pageTitle = 'Features | Smart Expense Tracking System';
$pageDescription = 'Explore all features of Smart Expense Tracking System - Real-time tracking, AI categorization, budget alerts, reports, and more.';
$pageKeywords = 'expense tracking features, budget management, financial reports, expense categorization, budget alerts';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" aria-labelledby="page-title">
    <div class="container">
        <h1 id="page-title" class="page-hero__title">Powerful Features</h1>
        <p class="page-hero__subtitle">
            Discover all the tools and capabilities that make Smart Expense
            Tracker the ultimate solution for managing your finances.
        </p>
    </div>
</section>

<!-- Features Grid Section -->
<section class="section" aria-labelledby="all-features-title">
    <div class="container">
        <header class="text-center" style="margin-bottom: var(--spacing-xl)">
            <h2 id="all-features-title">Everything You Need</h2>
            <p style="max-width: 600px; margin: 0 auto; color: var(--medium-gray);">
                A comprehensive suite of features designed to simplify every
                aspect of your expense management.
            </p>
        </header>
        
        <div class="features-grid">
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">💳</div>
                <h3 class="feature-card__title">Real-Time Tracking</h3>
                <p class="feature-card__text">
                    See your expenses the moment they happen. Bank connections
                    update in real-time for instant visibility.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">🤖</div>
                <h3 class="feature-card__title">AI Categorization</h3>
                <p class="feature-card__text">
                    Smart machine learning automatically categorizes your
                    transactions with 95%+ accuracy.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">📊</div>
                <h3 class="feature-card__title">Visual Reports</h3>
                <p class="feature-card__text">
                    Beautiful charts and graphs show your spending patterns at a
                    glance. Export to PDF or Excel.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">🔔</div>
                <h3 class="feature-card__title">Budget Alerts</h3>
                <p class="feature-card__text">
                    Set custom limits and get notified before you overspend. Stay on
                    track effortlessly.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">🏷️</div>
                <h3 class="feature-card__title">Custom Categories</h3>
                <p class="feature-card__text">
                    Create your own expense categories and tags to organize finances
                    your way.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">🏦</div>
                <h3 class="feature-card__title">Multi-Bank Support</h3>
                <p class="feature-card__text">
                    Connect accounts from over 10,000 financial institutions in one
                    unified dashboard.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">📱</div>
                <h3 class="feature-card__title">Mobile App</h3>
                <p class="feature-card__text">
                    Track expenses on the go with our iOS and Android apps. Scan
                    receipts instantly.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">🧾</div>
                <h3 class="feature-card__title">Receipt Scanner</h3>
                <p class="feature-card__text">
                    Snap a photo of receipts and let OCR extract all the details
                    automatically.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">👥</div>
                <h3 class="feature-card__title">Team Management</h3>
                <p class="feature-card__text">
                    Invite team members, set permissions, and manage expenses
                    collaboratively.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">📅</div>
                <h3 class="feature-card__title">Recurring Tracking</h3>
                <p class="feature-card__text">
                    Automatically track subscriptions and recurring payments. Never
                    miss a billing date.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">🌍</div>
                <h3 class="feature-card__title">Multi-Currency</h3>
                <p class="feature-card__text">
                    Track expenses in any currency with automatic conversion and
                    historical rates.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">🔐</div>
                <h3 class="feature-card__title">Bank-Level Security</h3>
                <p class="feature-card__text">
                    256-bit encryption and secure data handling keep your financial
                    info safe.
                </p>
            </article>
        </div>
    </div>
</section>

<!-- Integration Section -->
<section class="section section--light" aria-labelledby="integrations-title">
    <div class="container">
        <header class="text-center" style="margin-bottom: var(--spacing-xl)">
            <h2 id="integrations-title">Seamless Integrations</h2>
            <p style="max-width: 600px; margin: 0 auto; color: var(--medium-gray);">
                Connect with the tools you already use for a unified financial ecosystem.
            </p>
        </header>
        
        <div class="partners__grid" style="margin-bottom: var(--spacing-xl)">
            <img src="images/partner-visa.png" alt="Visa integration" class="partner-logo" width="100" height="40">
            <img src="images/partner-mastercard.png" alt="Mastercard integration" class="partner-logo" width="100" height="40">
            <img src="images/partner-paypal.png" alt="PayPal integration" class="partner-logo" width="100" height="40">
            <img src="images/partner-stripe.png" alt="Stripe integration" class="partner-logo" width="100" height="40">
            <img src="images/partner-quickbooks.png" alt="QuickBooks integration" class="partner-logo" width="100" height="40">
        </div>
        
        <div class="text-center">
            <p style="color: var(--medium-gray); margin-bottom: var(--spacing-md);">
                And over 10,000 more financial institutions
            </p>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn--secondary">Request an Integration</a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta" aria-labelledby="cta-title">
    <div class="container">
        <h2 id="cta-title" class="cta__title">Ready to Experience These Features?</h2>
        <p class="cta__text">
            Start your free 14-day trial and explore every feature. No credit card required.
        </p>
        <div class="cta__buttons">
            <a href="<?php echo SITE_URL; ?>/pricing.php" class="btn btn--white btn--lg">Start Free Trial</a>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn--outline-white btn--lg">Schedule Demo</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
