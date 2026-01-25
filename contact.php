<?php
/**
 * Smart Expense Tracking System - Contact Page
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 * 
 * Contact form with server-side validation and database storage
 * Second form implementation as required by assignment
 */

$pageTitle = 'Contact Us | Smart Expense Tracking System';
$pageDescription = 'Contact Smart Expense Tracking System - Get in touch with our team for support, sales inquiries, or partnership opportunities.';
$pageKeywords = 'contact us, customer support, expense tracker support, sales inquiry';

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$errors = [];
$success = false;
$formData = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'subject' => '',
    'message' => ''
];

// Pre-fill form if user is logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    $formData['name'] = $user['first_name'] . ' ' . $user['last_name'];
    $formData['email'] = $user['email'];
    $formData['phone'] = $user['phone'] ?? '';
}

// Process contact form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Collect and sanitize form data
        $formData['name'] = sanitizeInput($_POST['name'] ?? '');
        $formData['email'] = strtolower(trim($_POST['email'] ?? ''));
        $formData['phone'] = sanitizeInput($_POST['phone'] ?? '');
        $formData['subject'] = sanitizeInput($_POST['subject'] ?? '');
        $formData['message'] = sanitizeInput($_POST['message'] ?? '');
        
        // Validation
        if (empty($formData['name'])) {
            $errors[] = 'Please enter your name.';
        } elseif (strlen($formData['name']) < 2) {
            $errors[] = 'Name must be at least 2 characters.';
        }
        
        if (empty($formData['email'])) {
            $errors[] = 'Please enter your email address.';
        } elseif (!isValidEmail($formData['email'])) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (!empty($formData['phone']) && !isValidPhone($formData['phone'])) {
            $errors[] = 'Please enter a valid phone number.';
        }
        
        if (empty($formData['subject'])) {
            $errors[] = 'Please select a subject.';
        }
        
        if (empty($formData['message'])) {
            $errors[] = 'Please enter your message.';
        } elseif (strlen($formData['message']) < 10) {
            $errors[] = 'Message must be at least 10 characters.';
        }
        
        // Save to database
        if (empty($errors)) {
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (user_id, name, email, phone, subject, message, status)
                VALUES (?, ?, ?, ?, ?, ?, 'new')
            ");
            
            try {
                $result = $stmt->execute([
                    isLoggedIn() ? $_SESSION['user_id'] : null,
                    $formData['name'],
                    $formData['email'],
                    $formData['phone'] ?: null,
                    $formData['subject'],
                    $formData['message']
                ]);
                
                if ($result && $stmt->rowCount() > 0) {
                    logActivity('CONTACT_FORM', 'Contact form submitted by: ' . $formData['email']);
                    $success = true;
                    $formData = ['name' => '', 'email' => '', 'phone' => '', 'subject' => '', 'message' => ''];
                } else {
                    error_log("Contact Form: Insert returned false or 0 rows affected");
                    $errors[] = 'Failed to save your message. Please try again.';
                }
                
            } catch (PDOException $e) {
                error_log("Contact Form Error: " . $e->getMessage());
                $errors[] = 'An error occurred while sending your message. Please try again. Error: ' . $e->getMessage();
            }
        }
    }
}

$csrfToken = generateCSRFToken();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" aria-labelledby="page-title">
    <div class="container">
        <h1 id="page-title" class="page-hero__title">Contact Us</h1>
        <p class="page-hero__subtitle">
            Have questions? We'd love to hear from you. Send us a message and
            we'll respond as soon as possible.
        </p>
    </div>
</section>

<!-- Contact Section -->
<section class="section" aria-labelledby="contact-form-title">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-form">
                <h2 id="contact-form-title" style="margin-bottom: var(--spacing-lg)">
                    Send Us a Message
                </h2>
                
                <?php if ($success): ?>
                    <div class="form-success visible" role="alert">
                        <p style="margin: 0;">
                            <strong>Thank you!</strong> Your message has been sent
                            successfully. We'll get back to you within 24 hours.
                        </p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert--error" role="alert">
                        <ul style="margin: 0; padding-left: 1.5rem;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (!$success): ?>
                <form id="contact-form" method="POST" action="" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="form-group">
                        <label for="name" class="form-label">
                            Full Name <span class="required" aria-hidden="true">*</span>
                        </label>
                        <input type="text" id="name" name="name" class="form-input"
                               value="<?php echo htmlspecialchars($formData['name']); ?>"
                               required minlength="2" maxlength="100"
                               placeholder="Enter your full name"
                               aria-describedby="name-error">
                        <span class="form-error" id="name-error" role="alert"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">
                            Email Address <span class="required" aria-hidden="true">*</span>
                        </label>
                        <input type="email" id="email" name="email" class="form-input"
                               value="<?php echo htmlspecialchars($formData['email']); ?>"
                               required placeholder="Enter your email address"
                               aria-describedby="email-error">
                        <span class="form-error" id="email-error" role="alert"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-input"
                               value="<?php echo htmlspecialchars($formData['phone']); ?>"
                               placeholder="Enter your phone number (optional)"
                               aria-describedby="phone-error">
                        <span class="form-error" id="phone-error" role="alert"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="form-label">
                            Subject <span class="required" aria-hidden="true">*</span>
                        </label>
                        <select id="subject" name="subject" class="form-select" required
                                aria-describedby="subject-error">
                            <option value="">Select a subject</option>
                            <option value="general" <?php echo ($formData['subject'] === 'general') ? 'selected' : ''; ?>>General Inquiry</option>
                            <option value="support" <?php echo ($formData['subject'] === 'support') ? 'selected' : ''; ?>>Technical Support</option>
                            <option value="sales" <?php echo ($formData['subject'] === 'sales') ? 'selected' : ''; ?>>Sales Question</option>
                            <option value="billing" <?php echo ($formData['subject'] === 'billing') ? 'selected' : ''; ?>>Billing Issue</option>
                            <option value="partnership" <?php echo ($formData['subject'] === 'partnership') ? 'selected' : ''; ?>>Partnership Opportunity</option>
                            <option value="feedback" <?php echo ($formData['subject'] === 'feedback') ? 'selected' : ''; ?>>Feedback</option>
                            <option value="other" <?php echo ($formData['subject'] === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <span class="form-error" id="subject-error" role="alert"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label">
                            Message <span class="required" aria-hidden="true">*</span>
                        </label>
                        <textarea id="message" name="message" class="form-textarea"
                                  required minlength="10" maxlength="2000"
                                  placeholder="Type your message here..."
                                  aria-describedby="message-error"><?php echo htmlspecialchars($formData['message']); ?></textarea>
                        <span class="form-error" id="message-error" role="alert"></span>
                    </div>
                    
                    <button type="submit" class="btn btn--primary btn--lg" style="width: 100%;">
                        Send Message
                    </button>
                </form>
                <?php endif; ?>
            </div>
            
            <!-- Contact Information -->
            <div class="contact-info">
                <h2 style="margin-bottom: var(--spacing-lg)">Get in Touch</h2>
                
                <div class="contact-info__item">
                    <div class="contact-info__icon" aria-hidden="true">✉️</div>
                    <div>
                        <p class="contact-info__label">Email Us</p>
                        <p class="contact-info__text">
                            <a href="mailto:20034038@students.koi.edu.au">20034038@students.koi.edu.au</a>
                        </p>
                    </div>
                </div>
                
                <div class="contact-info__item">
                    <div class="contact-info__icon" aria-hidden="true">📞</div>
                    <div>
                        <p class="contact-info__label">Call Us</p>
                        <p class="contact-info__text">
                            <a href="tel:+61234567890">+61 (2) 3456-7890</a>
                        </p>
                    </div>
                </div>
                
                <div class="contact-info__item">
                    <div class="contact-info__icon" aria-hidden="true">📍</div>
                    <div>
                        <p class="contact-info__label">Visit Us</p>
                        <p class="contact-info__text">
                            Level 1, 545 Kent Street<br>Sydney, NSW 2000<br>Australia
                        </p>
                    </div>
                </div>
                
                <div class="contact-info__item">
                    <div class="contact-info__icon" aria-hidden="true">🕐</div>
                    <div>
                        <p class="contact-info__label">Business Hours</p>
                        <p class="contact-info__text">
                            Monday - Friday: 9:00 AM - 6:00 PM<br>
                            Saturday: 10:00 AM - 2:00 PM<br>
                            Sunday: Closed
                        </p>
                    </div>
                </div>
                
                <!-- Social Media Links -->
                <div style="margin-top: var(--spacing-xl)">
                    <p class="contact-info__label" style="margin-bottom: var(--spacing-sm)">
                        Follow Us
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link" aria-label="Follow us on Facebook">f</a>
                        <a href="#" class="social-link" aria-label="Follow us on Twitter">𝕏</a>
                        <a href="#" class="social-link" aria-label="Follow us on LinkedIn">in</a>
                        <a href="#" class="social-link" aria-label="Follow us on Instagram">📷</a>
                        <a href="#" class="social-link" aria-label="Follow us on YouTube">▶</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="section section--light" aria-labelledby="map-title">
    <div class="container">
        <header class="text-center" style="margin-bottom: var(--spacing-xl)">
            <h2 id="map-title">Find Us</h2>
            <p style="max-width: 600px; margin: 0 auto; color: var(--medium-gray);">
                Visit our headquarters in the heart of Sydney's business district.
            </p>
        </header>
        
        <div class="map-container">
            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%); display: flex; align-items: center; justify-content: center; color: white; text-align: center; padding: var(--spacing-lg);">
                <div>
                    <span style="font-size: 3rem; display: block; margin-bottom: var(--spacing-md);">📍</span>
                    <p style="font-size: 1.25rem; font-weight: 600; margin-bottom: var(--spacing-sm);">
                        Level 1, 545 Kent Street, Sydney
                    </p>
                    <p style="opacity: 0.9;">
                        Interactive map available with location services enabled
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta" aria-labelledby="cta-title">
    <div class="container">
        <h2 id="cta-title" class="cta__title">Ready to Get Started?</h2>
        <p class="cta__text">
            Join thousands of users who trust Smart Expense Tracker to manage
            their finances.
        </p>
        <div class="cta__buttons">
            <a href="<?php echo SITE_URL; ?>/pricing.php" class="btn btn--white btn--lg">View Pricing</a>
            <a href="<?php echo SITE_URL; ?>/features.php" class="btn btn--outline-white btn--lg">Explore Features</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
