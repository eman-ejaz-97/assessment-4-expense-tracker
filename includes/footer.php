    </main>
    <!-- End Main Content -->
    
    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="footer__grid">
                <div class="footer__brand">
                    <div class="footer__logo">
                        <span class="footer__logo-icon" aria-hidden="true">$</span>
                        <span>ExpenseTracker</span>
                    </div>
                    <p class="footer__text">
                        Smart Expense Tracking System helps you take control of your finances 
                        with real-time tracking, intelligent categorization, and actionable insights.
                    </p>
                </div>
                
                <div class="footer__column">
                    <h3 class="footer__title">Quick Links</h3>
                    <nav class="footer__links" aria-label="Footer quick links">
                        <a href="<?php echo SITE_URL; ?>/index.php" class="footer__link">Home</a>
                        <a href="<?php echo SITE_URL; ?>/about.php" class="footer__link">About Us</a>
                        <a href="<?php echo SITE_URL; ?>/features.php" class="footer__link">Features</a>
                        <a href="<?php echo SITE_URL; ?>/pricing.php" class="footer__link">Pricing</a>
                    </nav>
                </div>
                
                <div class="footer__column">
                    <h3 class="footer__title">Resources</h3>
                    <nav class="footer__links" aria-label="Footer resources">
                        <a href="<?php echo SITE_URL; ?>/gallery.php" class="footer__link">Gallery</a>
                        <a href="<?php echo SITE_URL; ?>/contact.php" class="footer__link">Contact</a>
                        <?php if (isLoggedIn()): ?>
                            <a href="<?php echo SITE_URL; ?>/dashboard/" class="footer__link">Dashboard</a>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/auth/login.php" class="footer__link">Login</a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/faq.php" class="footer__link">FAQ</a>
                    </nav>
                </div>
                
                <div class="footer__column">
                    <h3 class="footer__title">Legal</h3>
                    <nav class="footer__links" aria-label="Footer legal">
                        <a href="<?php echo SITE_URL; ?>/privacy-policy.php" class="footer__link">Privacy Policy</a>
                        <a href="<?php echo SITE_URL; ?>/terms.php" class="footer__link">Terms of Service</a>
                        <a href="<?php echo SITE_URL; ?>/cookies.php" class="footer__link">Cookie Policy</a>
                        <a href="<?php echo SITE_URL; ?>/security.php" class="footer__link">Security</a>
                    </nav>
                </div>
            </div>
            
            <div class="footer__bottom">
                <p class="footer__copyright">
                    &copy; <?php echo date('Y'); ?> Smart Expense Tracking System. All rights reserved.<br>
                    Assessment 4 - Student ID: 20034038 | ICT726 Web Development
                </p>
                <div class="footer__socials">
                    <a href="#" class="footer__social" aria-label="Follow us on Facebook">
                        <span aria-hidden="true">f</span>
                    </a>
                    <a href="#" class="footer__social" aria-label="Follow us on Twitter">
                        <span aria-hidden="true">𝕏</span>
                    </a>
                    <a href="#" class="footer__social" aria-label="Follow us on LinkedIn">
                        <span aria-hidden="true">in</span>
                    </a>
                    <a href="#" class="footer__social" aria-label="Follow us on Instagram">
                        <span aria-hidden="true">📷</span>
                    </a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
