<?php
/**
 * Smart Expense Tracking System - Pricing Page
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 */

$pageTitle = 'Pricing | Smart Expense Tracking System';
$pageDescription = 'Simple, transparent pricing for Smart Expense Tracking System. Choose the plan that fits your needs.';
$pageKeywords = 'pricing, subscription plans, expense tracker pricing, budget management cost';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" aria-labelledby="page-title">
    <div class="container">
        <h1 id="page-title" class="page-hero__title">Simple, Transparent Pricing</h1>
        <p class="page-hero__subtitle">
            Choose the plan that fits your needs. All plans include a 14-day free trial.
        </p>
    </div>
</section>

<!-- Pricing Section -->
<section class="section" aria-labelledby="pricing-title">
    <div class="container">
        <!-- Billing Toggle -->
        <div class="pricing-toggle" role="group" aria-label="Billing frequency">
            <span class="pricing-toggle__label active" id="monthly-label">Monthly</span>
            <button class="pricing-toggle__switch" id="billing-toggle" aria-pressed="false" aria-label="Toggle between monthly and yearly billing">
                <span class="sr-only">Toggle billing frequency</span>
            </button>
            <span class="pricing-toggle__label" id="yearly-label">Yearly</span>
            <span class="pricing-toggle__badge">Save 20%</span>
        </div>
        
        <h2 id="pricing-title" class="sr-only">Pricing Plans</h2>
        
        <!-- Pricing Cards -->
        <div class="pricing-grid">
            <!-- Basic Plan -->
            <article class="pricing-card animate-on-scroll" aria-labelledby="basic-title">
                <h3 id="basic-title" class="pricing-card__name">Basic</h3>
                <div class="pricing-card__price" aria-label="9 dollars per month">
                    <sup>$</sup><span class="price-value">9</span><span>/month</span>
                </div>
                <p style="color: var(--medium-gray); font-size: 0.9rem;">
                    Perfect for individuals getting started
                </p>
                <ul class="pricing-card__features">
                    <li class="pricing-card__feature">Up to 3 bank accounts</li>
                    <li class="pricing-card__feature">Basic expense tracking</li>
                    <li class="pricing-card__feature">Monthly reports</li>
                    <li class="pricing-card__feature">Email support</li>
                    <li class="pricing-card__feature pricing-card__feature--disabled">AI categorization</li>
                    <li class="pricing-card__feature pricing-card__feature--disabled">Custom categories</li>
                    <li class="pricing-card__feature pricing-card__feature--disabled">Team access</li>
                </ul>
                <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn--secondary" style="width: 100%;">Get Started</a>
            </article>
            
            <!-- Pro Plan (Featured) -->
            <article class="pricing-card pricing-card--featured animate-on-scroll" aria-labelledby="pro-title">
                <span class="pricing-card__badge">Most Popular</span>
                <h3 id="pro-title" class="pricing-card__name">Professional</h3>
                <div class="pricing-card__price" aria-label="29 dollars per month">
                    <sup>$</sup><span class="price-value">29</span><span>/month</span>
                </div>
                <p style="color: var(--medium-gray); font-size: 0.9rem;">
                    Best for professionals & small teams
                </p>
                <ul class="pricing-card__features">
                    <li class="pricing-card__feature">Unlimited bank accounts</li>
                    <li class="pricing-card__feature">Advanced expense tracking</li>
                    <li class="pricing-card__feature">Weekly & monthly reports</li>
                    <li class="pricing-card__feature">Priority email support</li>
                    <li class="pricing-card__feature">AI categorization</li>
                    <li class="pricing-card__feature">Custom categories</li>
                    <li class="pricing-card__feature pricing-card__feature--disabled">Team access (up to 5)</li>
                </ul>
                <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn--primary" style="width: 100%;">Get Started</a>
            </article>
            
            <!-- Enterprise Plan -->
            <article class="pricing-card animate-on-scroll" aria-labelledby="enterprise-title">
                <h3 id="enterprise-title" class="pricing-card__name">Enterprise</h3>
                <div class="pricing-card__price" aria-label="79 dollars per month">
                    <sup>$</sup><span class="price-value">79</span><span>/month</span>
                </div>
                <p style="color: var(--medium-gray); font-size: 0.9rem;">
                    For businesses with advanced needs
                </p>
                <ul class="pricing-card__features">
                    <li class="pricing-card__feature">Unlimited everything</li>
                    <li class="pricing-card__feature">Enterprise expense tracking</li>
                    <li class="pricing-card__feature">Custom report schedules</li>
                    <li class="pricing-card__feature">24/7 phone & email support</li>
                    <li class="pricing-card__feature">Advanced AI categorization</li>
                    <li class="pricing-card__feature">Custom categories & rules</li>
                    <li class="pricing-card__feature">Unlimited team access</li>
                </ul>
                <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn--secondary" style="width: 100%;">Contact Sales</a>
            </article>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section" aria-labelledby="faq-title">
    <div class="container">
        <header class="text-center" style="margin-bottom: var(--spacing-xl)">
            <h2 id="faq-title">Frequently Asked Questions</h2>
            <p style="max-width: 600px; margin: 0 auto; color: var(--medium-gray);">
                Got questions? We've got answers.
            </p>
        </header>
        
        <div class="faq-list">
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Can I change my plan later?</span>
                    <span class="faq-question__icon" aria-hidden="true">+</span>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer__content">
                        <p>Absolutely! You can upgrade or downgrade your plan at any time. When you upgrade, you'll be prorated for the remainder of your billing cycle.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Is there a free trial?</span>
                    <span class="faq-question__icon" aria-hidden="true">+</span>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer__content">
                        <p>Yes! All plans come with a 14-day free trial with full access to all features. No credit card required to start.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Is my data secure?</span>
                    <span class="faq-question__icon" aria-hidden="true">+</span>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer__content">
                        <p>Security is our top priority. We use bank-level 256-bit AES encryption, secure data centers, and never store your bank credentials. We're also SOC 2 Type II certified and GDPR compliant.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Can I cancel anytime?</span>
                    <span class="faq-question__icon" aria-hidden="true">+</span>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer__content">
                        <p>Yes, you can cancel your subscription at any time with no questions asked. Your access will continue until the end of your current billing period.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta" aria-labelledby="cta-title">
    <div class="container">
        <h2 id="cta-title" class="cta__title">Need a Custom Solution?</h2>
        <p class="cta__text">
            Have specific requirements? Our team can create a tailored plan that fits your organization's unique needs.
        </p>
        <div class="cta__buttons">
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn--white btn--lg">Contact Sales</a>
            <a href="<?php echo SITE_URL; ?>/features.php" class="btn btn--outline-white btn--lg">View All Features</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
