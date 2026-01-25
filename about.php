<?php
/**
 * Smart Expense Tracking System - About Us Page
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 */

$pageTitle = 'About Us | Smart Expense Tracking System';
$pageDescription = 'Learn about Smart Expense Tracking System - Our story, mission, values, and the team behind the product.';
$pageKeywords = 'about us, company story, team, mission, expense tracking company';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" aria-labelledby="page-title">
    <div class="container">
        <h1 id="page-title" class="page-hero__title">About Us</h1>
        <p class="page-hero__subtitle">
            Discover our story, meet the team, and learn about the values that
            drive us to help you achieve financial success.
        </p>
    </div>
</section>

<!-- Our Story Section -->
<section class="section" aria-labelledby="story-title">
    <div class="container">
        <div class="spotlight">
            <div class="spotlight__image">
                <img src="images/about%20us.png" 
                     alt="Team working together on financial technology solutions"
                     width="600" height="400">
            </div>
            <div class="spotlight__content">
                <span class="spotlight__label">Our Story</span>
                <h2 id="story-title" class="spotlight__title">
                    From Spreadsheets to Smart Tracking
                </h2>
                <p class="spotlight__text">
                    Founded in early 2025, Smart Expense Tracking System was born
                    from a simple frustration: managing personal and business
                    finances shouldn't require an accounting degree or hours of
                    manual data entry.
                </p>
                <p class="spotlight__text">
                    Muhammad Eman Ejaz, our Founder and CEO, brought together a team
                    of passionate professionals to turn this vision into reality.
                    Alongside Muhammad Waqas Iqbal, who leads our financial strategy
                    as Chief Financial Officer, and Sajina Rana Magar, who heads our
                    Communications, they created a tool that makes expense tracking
                    intuitive, automated, and actually enjoyable.
                </p>
                <p class="spotlight__text">
                    Today, our team continues to innovate and expand our
                    capabilities, driven by user feedback and our commitment to
                    financial empowerment for all.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="section section--dark" aria-labelledby="mission-title">
    <div class="container text-center">
        <h2 id="mission-title" style="color: var(--white); margin-bottom: var(--spacing-md);">
            Our Mission
        </h2>
        <p style="max-width: 800px; margin: 0 auto; font-size: 1.25rem; opacity: 0.95;">
            To democratize financial management by providing powerful,
            user-friendly tools that help individuals and businesses make
            informed decisions, save money, and achieve their financial
            goals—regardless of their technical or financial expertise.
        </p>
    </div>
</section>

<!-- Core Values Section -->
<section class="section section--light" aria-labelledby="values-title">
    <div class="container">
        <header class="text-center" style="margin-bottom: var(--spacing-xl)">
            <h2 id="values-title">Our Core Values</h2>
            <p style="max-width: 600px; margin: 0 auto; color: var(--medium-gray);">
                The principles that guide every decision we make and every feature we build.
            </p>
        </header>
        
        <div class="values-grid">
            <article class="value-card animate-on-scroll">
                <div class="value-card__icon" aria-hidden="true">🎯</div>
                <h3 class="value-card__title">Simplicity First</h3>
                <p class="value-card__text">
                    We believe powerful tools don't have to be complicated. Every
                    feature is designed for ease of use without sacrificing functionality.
                </p>
            </article>
            
            <article class="value-card animate-on-scroll">
                <div class="value-card__icon" aria-hidden="true">🔒</div>
                <h3 class="value-card__title">Security & Privacy</h3>
                <p class="value-card__text">
                    Your financial data is sacred. We employ bank-level encryption
                    and never sell your information to third parties.
                </p>
            </article>
            
            <article class="value-card animate-on-scroll">
                <div class="value-card__icon" aria-hidden="true">💡</div>
                <h3 class="value-card__title">Continuous Innovation</h3>
                <p class="value-card__text">
                    We're always learning and improving. User feedback drives our
                    roadmap, ensuring we build what you actually need.
                </p>
            </article>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="section" aria-labelledby="team-title">
    <div class="container">
        <header class="text-center" style="margin-bottom: var(--spacing-xl)">
            <h2 id="team-title">Meet Our Team</h2>
            <p style="max-width: 600px; margin: 0 auto; color: var(--medium-gray);">
                The passionate people working behind the scenes to make your
                financial management effortless.
            </p>
        </header>
        
        <div class="team-grid">
            <article class="team-card animate-on-scroll">
                <div class="team-card__image">
                    <img src="images/founder.jpeg" 
                         alt="Portrait of Muhammad Eman Ejaz, Founder and CEO"
                         width="120" height="120">
                </div>
                <h3 class="team-card__name">Muhammad Eman Ejaz</h3>
                <p class="team-card__role">Founder & CEO</p>
                <div class="team-card__socials">
                    <a href="#" class="team-card__social" aria-label="LinkedIn">in</a>
                    <a href="#" class="team-card__social" aria-label="Twitter">𝕏</a>
                </div>
            </article>
            
            <article class="team-card animate-on-scroll">
                <div class="team-card__image">
                    <img src="images/waqas-CFO.jpeg" 
                         alt="Portrait of Muhammad Waqas Iqbal, Chief Financial Officer"
                         width="120" height="120">
                </div>
                <h3 class="team-card__name">Muhammad Waqas Iqbal</h3>
                <p class="team-card__role">Chief Financial Officer</p>
                <div class="team-card__socials">
                    <a href="#" class="team-card__social" aria-label="LinkedIn">in</a>
                    <a href="#" class="team-card__social" aria-label="Twitter">𝕏</a>
                </div>
            </article>
            
            <article class="team-card animate-on-scroll">
                <div class="team-card__image">
                    <img src="images/sajina-communication-head.jpg" 
                         alt="Portrait of Sajina Rana Magar, Head of Communications"
                         width="120" height="120">
                </div>
                <h3 class="team-card__name">Sajina Rana Magar</h3>
                <p class="team-card__role">Head of Communications</p>
                <div class="team-card__socials">
                    <a href="#" class="team-card__social" aria-label="LinkedIn">in</a>
                    <a href="#" class="team-card__social" aria-label="Twitter">𝕏</a>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- Timeline Section -->
<section class="section section--light" aria-labelledby="timeline-title">
    <div class="container">
        <header class="text-center" style="margin-bottom: var(--spacing-xl)">
            <h2 id="timeline-title">Our Journey</h2>
            <p style="max-width: 600px; margin: 0 auto; color: var(--medium-gray);">
                Key milestones that have shaped our company and product.
            </p>
        </header>
        
        <div class="timeline">
            <div class="timeline__item animate-on-scroll">
                <div class="timeline__content">
                    <span class="timeline__year">2020</span>
                    <h3 class="timeline__title">The Beginning</h3>
                    <p class="timeline__text">
                        Smart Expense Tracker was founded in a small garage by two
                        friends frustrated with existing expense management tools.
                    </p>
                </div>
            </div>
            
            <div class="timeline__item animate-on-scroll">
                <div class="timeline__content">
                    <span class="timeline__year">2021</span>
                    <h3 class="timeline__title">First 1,000 Users</h3>
                    <p class="timeline__text">
                        Launched our beta version and reached our first 1,000 active
                        users within three months of release.
                    </p>
                </div>
            </div>
            
            <div class="timeline__item animate-on-scroll">
                <div class="timeline__content">
                    <span class="timeline__year">2022</span>
                    <h3 class="timeline__title">Series A Funding</h3>
                    <p class="timeline__text">
                        Secured $5 million in Series A funding to expand our team and
                        accelerate product development.
                    </p>
                </div>
            </div>
            
            <div class="timeline__item animate-on-scroll">
                <div class="timeline__content">
                    <span class="timeline__year">2023</span>
                    <h3 class="timeline__title">50,000 Users</h3>
                    <p class="timeline__text">
                        Reached 50,000 active users and launched integrations with
                        major banking platforms and payment processors.
                    </p>
                </div>
            </div>
            
            <div class="timeline__item animate-on-scroll">
                <div class="timeline__content">
                    <span class="timeline__year">2024</span>
                    <h3 class="timeline__title">AI-Powered Features</h3>
                    <p class="timeline__text">
                        Introduced AI-powered expense categorization and predictive
                        budgeting, revolutionizing how users manage their finances.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta" aria-labelledby="cta-title">
    <div class="container">
        <h2 id="cta-title" class="cta__title">Join Our Growing Community</h2>
        <p class="cta__text">
            Become part of a community that's transforming how people manage
            their finances. Your journey to financial freedom starts here.
        </p>
        <div class="cta__buttons">
            <a href="<?php echo SITE_URL; ?>/pricing.php" class="btn btn--white btn--lg">Get Started Today</a>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn--outline-white btn--lg">Get in Touch</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
