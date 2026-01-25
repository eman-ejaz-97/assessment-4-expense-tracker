<?php
/**
 * Smart Expense Tracking System - Privacy Policy
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Privacy notice explaining how user data is collected, used, and protected
 * As required by Assignment 4 for privacy and security compliance
 */

$pageTitle = 'Privacy Policy | Smart Expense Tracking System';
$pageDescription = 'Read our privacy policy to understand how Smart Expense Tracker collects, uses, and protects your personal and financial data.';
$pageKeywords = 'privacy policy, data protection, user privacy, GDPR, data security';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" aria-labelledby="page-title">
    <div class="container">
        <h1 id="page-title" class="page-hero__title">Privacy Policy</h1>
        <p class="page-hero__subtitle">
            Your privacy is important to us. This policy explains how we collect, use, and protect your data.
        </p>
    </div>
</section>

<!-- Privacy Policy Content -->
<section class="section" aria-labelledby="privacy-content">
    <div class="container">
        <div class="legal-content">
            <p class="legal-content__updated">
                <strong>Last Updated:</strong> <?php echo date('F d, Y'); ?>
            </p>
            
            <article class="legal-section">
                <h2>1. Introduction</h2>
                <p>
                    Smart Expense Tracking System ("we," "our," or "us") is committed to protecting your privacy. 
                    This Privacy Policy explains how we collect, use, disclose, and safeguard your information 
                    when you use our expense tracking application and website (collectively, the "Service").
                </p>
                <p>
                    By using our Service, you agree to the collection and use of information in accordance 
                    with this policy. If you do not agree with our policies and practices, please do not use our Service.
                </p>
            </article>
            
            <article class="legal-section">
                <h2>2. Information We Collect</h2>
                
                <h3>2.1 Personal Information</h3>
                <p>We collect information that you provide directly to us, including:</p>
                <ul>
                    <li><strong>Account Information:</strong> Name, email address, username, password (encrypted), and phone number</li>
                    <li><strong>Profile Information:</strong> Profile picture and preferences</li>
                    <li><strong>Financial Data:</strong> Expense records, categories, payment methods, and budget information you choose to enter</li>
                    <li><strong>Communications:</strong> Messages sent through our contact form or support channels</li>
                </ul>
                
                <h3>2.2 Automatically Collected Information</h3>
                <p>When you access our Service, we automatically collect:</p>
                <ul>
                    <li><strong>Log Data:</strong> IP address, browser type, operating system, referring URLs, and access times</li>
                    <li><strong>Device Information:</strong> Device type, unique device identifiers, and mobile network information</li>
                    <li><strong>Usage Data:</strong> Pages viewed, features used, and actions taken within the Service</li>
                    <li><strong>Cookies:</strong> Session cookies for authentication and preference cookies for user experience</li>
                </ul>
            </article>
            
            <article class="legal-section">
                <h2>3. How We Use Your Information</h2>
                <p>We use the collected information for the following purposes:</p>
                <ul>
                    <li><strong>Service Provision:</strong> To provide, maintain, and improve our expense tracking Service</li>
                    <li><strong>Account Management:</strong> To create and manage your account, authenticate users, and provide customer support</li>
                    <li><strong>Analytics:</strong> To understand how users interact with our Service and improve user experience</li>
                    <li><strong>Communication:</strong> To send you service updates, security alerts, and support messages</li>
                    <li><strong>Security:</strong> To detect, prevent, and address technical issues and fraudulent activity</li>
                    <li><strong>Legal Compliance:</strong> To comply with legal obligations and protect our rights</li>
                </ul>
            </article>
            
            <article class="legal-section">
                <h2>4. Data Security</h2>
                <p>
                    We implement robust security measures to protect your personal and financial information:
                </p>
                <ul>
                    <li><strong>Password Hashing:</strong> All passwords are securely hashed using bcrypt algorithm with salt</li>
                    <li><strong>Session Security:</strong> Secure session management with regular regeneration and timeout</li>
                    <li><strong>Input Validation:</strong> All user inputs are sanitized to prevent SQL injection and XSS attacks</li>
                    <li><strong>CSRF Protection:</strong> Token-based protection against cross-site request forgery</li>
                    <li><strong>Access Control:</strong> Role-based access control (RBAC) to limit data access</li>
                    <li><strong>Encryption:</strong> Data transmitted over HTTPS using TLS encryption</li>
                    <li><strong>Account Lockout:</strong> Protection against brute force attacks with automatic account lockout</li>
                </ul>
            </article>
            
            <article class="legal-section">
                <h2>5. Data Retention</h2>
                <p>
                    We retain your personal information for as long as your account is active or as needed to 
                    provide you services. We will retain and use your information as necessary to:
                </p>
                <ul>
                    <li>Comply with our legal obligations</li>
                    <li>Resolve disputes</li>
                    <li>Enforce our agreements</li>
                </ul>
                <p>
                    You may request deletion of your account and associated data at any time by contacting us.
                </p>
            </article>
            
            <article class="legal-section">
                <h2>6. Information Sharing</h2>
                <p>
                    We do <strong>NOT</strong> sell, trade, or rent your personal information to third parties. 
                    We may share your information only in the following circumstances:
                </p>
                <ul>
                    <li><strong>With Your Consent:</strong> When you have given us explicit permission</li>
                    <li><strong>Service Providers:</strong> With trusted third-party service providers who assist in operating our Service (under strict confidentiality agreements)</li>
                    <li><strong>Legal Requirements:</strong> When required by law, court order, or governmental authority</li>
                    <li><strong>Protection of Rights:</strong> To protect our rights, privacy, safety, or property</li>
                </ul>
            </article>
            
            <article class="legal-section">
                <h2>7. Your Rights</h2>
                <p>You have the following rights regarding your personal data:</p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of the personal data we hold about you</li>
                    <li><strong>Correction:</strong> Request correction of inaccurate or incomplete data</li>
                    <li><strong>Deletion:</strong> Request deletion of your personal data</li>
                    <li><strong>Portability:</strong> Request a copy of your data in a structured, machine-readable format</li>
                    <li><strong>Withdrawal:</strong> Withdraw consent for processing where consent was the basis</li>
                    <li><strong>Objection:</strong> Object to processing of your personal data</li>
                </ul>
                <p>
                    To exercise these rights, please contact us at 
                    <a href="mailto:privacy@expensetracker.com">privacy@expensetracker.com</a>.
                </p>
            </article>
            
            <article class="legal-section">
                <h2>8. Cookies Policy</h2>
                <p>
                    We use cookies and similar tracking technologies to enhance your experience:
                </p>
                <ul>
                    <li><strong>Essential Cookies:</strong> Required for authentication and session management</li>
                    <li><strong>Preference Cookies:</strong> Remember your settings and preferences</li>
                    <li><strong>Analytics Cookies:</strong> Help us understand how you use our Service</li>
                </ul>
                <p>
                    You can control cookies through your browser settings. However, disabling essential cookies 
                    may affect the functionality of our Service.
                </p>
            </article>
            
            <article class="legal-section">
                <h2>9. Children's Privacy</h2>
                <p>
                    Our Service is not intended for individuals under the age of 18. We do not knowingly collect 
                    personal information from children. If we become aware that we have collected personal 
                    information from a child, we will take steps to delete such information.
                </p>
            </article>
            
            <article class="legal-section">
                <h2>10. Changes to This Policy</h2>
                <p>
                    We may update this Privacy Policy from time to time. We will notify you of any changes by 
                    posting the new Privacy Policy on this page and updating the "Last Updated" date. We 
                    encourage you to review this Privacy Policy periodically.
                </p>
            </article>
            
            <article class="legal-section">
                <h2>11. Contact Us</h2>
                <p>
                    If you have any questions about this Privacy Policy or our data practices, please contact us:
                </p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:20034038@students.koi.edu.au">20034038@students.koi.edu.au</a></li>
                    <li><strong>Address:</strong> Level 1, 545 Kent Street, Sydney, NSW 2000, Australia</li>
                    <li><strong>Phone:</strong> +61 (2) 3456-7890</li>
                </ul>
            </article>
            
            <div class="legal-section legal-section--highlight">
                <h2>Privacy Commitment</h2>
                <p>
                    At Smart Expense Tracking System, we are committed to handling your data responsibly and 
                    transparently. Your trust is our priority, and we continually work to protect your privacy 
                    while providing you with the best expense tracking experience.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta" aria-labelledby="cta-title">
    <div class="container">
        <h2 id="cta-title" class="cta__title">Have Questions About Your Privacy?</h2>
        <p class="cta__text">
            We're here to help. Contact our privacy team for any questions or concerns.
        </p>
        <div class="cta__buttons">
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn--white btn--lg">Contact Us</a>
            <a href="<?php echo SITE_URL; ?>/terms.php" class="btn btn--outline-white btn--lg">Terms of Service</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
