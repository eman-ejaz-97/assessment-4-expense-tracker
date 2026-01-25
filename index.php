<?php
/**
 * Smart Expense Tracking System - Homepage
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Main landing page with dynamic content and SEO optimization
 */

$pageTitle = 'Smart Expense Tracking System | Home';
$pageDescription = 'Smart Expense Tracking System - Track expenses in real-time, categorize spending automatically, generate reports, and set custom budget alerts.';
$pageKeywords = 'expense tracking, budget management, financial technology, expense reports, budget alerts';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" aria-labelledby="hero-title">
    <div class="container">
        <div class="hero__inner">
            <div class="hero__content">
                <span class="hero__badge">New Release 2025</span>
                <h1 id="hero-title" class="hero__title">
                    Take Control of Your Finances Today
                </h1>
                <p class="hero__subtitle">
                    Track expenses in real-time, categorize spending automatically,
                    generate insightful reports, and set custom budget alerts. Your
                    financial wellness journey starts here.
                </p>
                <div class="hero__buttons">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>/dashboard/" class="btn btn--primary btn--lg">Go to Dashboard</a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn--primary btn--lg">Get Started Free</a>
                        <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn--secondary btn--lg">Login</a>
                    <?php endif; ?>
                </div>
                <div class="hero__stats">
                    <div class="stat">
                        <div class="stat__number">50K+</div>
                        <div class="stat__label">Active Users</div>
                    </div>
                    <div class="stat">
                        <div class="stat__number">$2M+</div>
                        <div class="stat__label">Tracked Monthly</div>
                    </div>
                    <div class="stat">
                        <div class="stat__number">4.9★</div>
                        <div class="stat__label">User Rating</div>
                    </div>
                </div>
            </div>
            <div class="hero__image">
                <img src="images/hero-dashboard.png" 
                     alt="Smart Expense Tracker dashboard showing expense analytics and budget overview"
                     width="600" height="450">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section section--light" aria-labelledby="features-title">
    <div class="container">
        <header class="text-center" style="margin-bottom: var(--spacing-xl)">
            <h2 id="features-title">Why Choose Our Expense Tracker?</h2>
            <p style="max-width: 600px; margin: 0 auto; color: var(--medium-gray);">
                Powerful features designed to simplify your financial management
                and help you achieve your savings goals.
            </p>
        </header>
        
        <div class="features-grid">
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">💰</div>
                <h3 class="feature-card__title">Real-Time Tracking</h3>
                <p class="feature-card__text">
                    Monitor your expenses as they happen. Every transaction is
                    instantly recorded and categorized for your convenience.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">⏱️</div>
                <h3 class="feature-card__title">Smart Automation</h3>
                <p class="feature-card__text">
                    AI-powered categorization automatically sorts your expenses,
                    saving you hours of manual data entry each month.
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <div class="feature-card__icon" aria-hidden="true">🔔</div>
                <h3 class="feature-card__title">Budget Alerts</h3>
                <p class="feature-card__text">
                    Set custom spending limits and receive instant notifications
                    when you're approaching your budget thresholds.
                </p>
            </article>
        </div>
    </div>
</section>

<!-- Partners Section -->
<section class="partners" aria-labelledby="partners-title">
    <div class="container">
        <h2 id="partners-title" class="partners__title">Trusted by Industry Leaders</h2>
        <div class="partners__grid">
            <img src="images/partner-visa.png" alt="Visa" class="partner-logo" width="100" height="40">
            <img src="images/partner-mastercard.png" alt="Mastercard" class="partner-logo" width="100" height="40">
            <img src="images/partner-paypal.png" alt="PayPal" class="partner-logo" width="100" height="40">
            <img src="images/partner-stripe.png" alt="Stripe" class="partner-logo" width="100" height="40">
            <img src="images/partner-quickbooks.png" alt="QuickBooks" class="partner-logo" width="100" height="40">
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="section" aria-labelledby="how-it-works-title">
    <div class="container">
        <header class="text-center" style="margin-bottom: var(--spacing-xl)">
            <h2 id="how-it-works-title">How It Works</h2>
            <p style="max-width: 600px; margin: 0 auto; color: var(--medium-gray);">
                Get started in three simple steps and begin your journey to
                financial freedom.
            </p>
        </header>
        
        <div class="steps">
            <div class="step animate-on-scroll">
                <h3 class="step__title">Create Your Account</h3>
                <p class="step__text">
                    Sign up in seconds with your email or connect through your
                    preferred social platform. No credit card required.
                </p>
            </div>
            
            <div class="step animate-on-scroll">
                <h3 class="step__title">Add Your Expenses</h3>
                <p class="step__text">
                    Easily log your daily expenses, categorize them, and track
                    your spending patterns with our intuitive interface.
                </p>
            </div>
            
            <div class="step animate-on-scroll">
                <h3 class="step__title">Track & Optimize</h3>
                <p class="step__text">
                    Watch your spending patterns emerge, set budgets, receive
                    insights, and make smarter financial decisions.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonial Section -->
<section class="section section--light" aria-labelledby="testimonials-title">
    <div class="container">
        <header class="text-center" style="margin-bottom: var(--spacing-xl)">
            <h2 id="testimonials-title">What Our Users Say</h2>
        </header>
        
        <div class="features-grid">
            <article class="feature-card animate-on-scroll">
                <p class="feature-card__text" style="font-style: italic; margin-bottom: var(--spacing-md);">
                    "This app completely transformed how I manage my finances. I've
                    saved over $3,000 in just six months!"
                </p>
                <p style="font-weight: 600; color: var(--primary-color);">
                    — Sarah M., Small Business Owner
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <p class="feature-card__text" style="font-style: italic; margin-bottom: var(--spacing-md);">
                    "The automatic categorization is incredible. It saves me hours
                    every week that I used to spend on spreadsheets."
                </p>
                <p style="font-weight: 600; color: var(--primary-color);">
                    — James K., Freelance Designer
                </p>
            </article>
            
            <article class="feature-card animate-on-scroll">
                <p class="feature-card__text" style="font-style: italic; margin-bottom: var(--spacing-md);">
                    "Finally, an expense tracker that's both powerful and easy to
                    use. Highly recommend to anyone serious about budgeting."
                </p>
                <p style="font-weight: 600; color: var(--primary-color);">
                    — Lisa T., Marketing Director
                </p>
            </article>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta" aria-labelledby="cta-title">
    <div class="container">
        <h2 id="cta-title" class="cta__title">
            Ready to Take Control of Your Finances?
        </h2>
        <p class="cta__text">
            Join over 50,000 users who are already saving smarter. Start your
            free trial today—no credit card required.
        </p>
        <div class="cta__buttons">
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo SITE_URL; ?>/dashboard/" class="btn btn--white btn--lg">Go to Dashboard</a>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn--white btn--lg">Get Started Now</a>
                <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn--outline-white btn--lg">Contact Sales</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
