<?php
/**
 * Smart Expense Tracking System - Gallery Page
 * Student ID: 20034038
 * Assessment 4 - ICT726 Web Development
 */

$pageTitle = 'Gallery | Smart Expense Tracking System';
$pageDescription = 'View screenshots, videos, and visual media showcasing the Smart Expense Tracking System interface and features.';
$pageKeywords = 'expense tracker screenshots, app gallery, product demo, interface preview';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" aria-labelledby="page-title">
    <div class="container">
        <h1 id="page-title" class="page-hero__title">Gallery</h1>
        <p class="page-hero__subtitle">
            Explore screenshots, videos, and visuals showcasing the Smart
            Expense Tracking System in action.
        </p>
    </div>
</section>

<!-- Image Gallery Section -->
<section class="section" aria-labelledby="gallery-title">
    <div class="container">
        <header class="text-center" style="margin-bottom: var(--spacing-xl)">
            <h2 id="gallery-title">Product Screenshots</h2>
            <p style="max-width: 600px; margin: 0 auto; color: var(--medium-gray);">
                Click on any image to view it in full size.
            </p>
        </header>
        
        <!-- Gallery Filters -->
        <div class="gallery-filters" role="group" aria-label="Filter gallery images">
            <button class="gallery-filter active" data-filter="all">All</button>
            <button class="gallery-filter" data-filter="dashboard">Dashboard</button>
            <button class="gallery-filter" data-filter="reports">Reports</button>
            <button class="gallery-filter" data-filter="mobile">Mobile</button>
        </div>
        
        <!-- Gallery Grid -->
        <div class="gallery-grid" role="list">
            <div class="gallery-item gallery-item--large" data-category="dashboard" role="listitem">
                <img src="images/gallery-1.png" alt="Main dashboard overview showing expense summary and recent transactions" loading="lazy">
                <div class="gallery-item__overlay">
                    <span class="gallery-item__title">Main Dashboard</span>
                </div>
            </div>
            
            <div class="gallery-item" data-category="reports" role="listitem">
                <img src="images/gallery-2.png" alt="Monthly expense report with category breakdown chart" loading="lazy">
                <div class="gallery-item__overlay">
                    <span class="gallery-item__title">Monthly Reports</span>
                </div>
            </div>
            
            <div class="gallery-item" data-category="dashboard" role="listitem">
                <img src="images/gallery-3.png" alt="Budget tracking view with progress bars for each category" loading="lazy">
                <div class="gallery-item__overlay">
                    <span class="gallery-item__title">Budget Tracking</span>
                </div>
            </div>
            
            <div class="gallery-item" data-category="mobile" role="listitem">
                <img src="images/gallery-4.png" alt="Mobile app home screen showing quick expense entry" loading="lazy">
                <div class="gallery-item__overlay">
                    <span class="gallery-item__title">Mobile App</span>
                </div>
            </div>
            
            <div class="gallery-item" data-category="reports" role="listitem">
                <img src="images/gallery-5.png" alt="Annual spending trends visualization with yearly comparison" loading="lazy">
                <div class="gallery-item__overlay">
                    <span class="gallery-item__title">Spending Trends</span>
                </div>
            </div>
            
            <div class="gallery-item gallery-item--large" data-category="dashboard" role="listitem">
                <img src="images/gallery-6.png" alt="Transaction list view with search and filter options" loading="lazy">
                <div class="gallery-item__overlay">
                    <span class="gallery-item__title">Transaction History</span>
                </div>
            </div>
            
            <div class="gallery-item" data-category="mobile" role="listitem">
                <img src="images/gallery-7.png" alt="Mobile receipt scanner capturing expense details" loading="lazy">
                <div class="gallery-item__overlay">
                    <span class="gallery-item__title">Receipt Scanner</span>
                </div>
            </div>
            
            <div class="gallery-item" data-category="reports" role="listitem">
                <img src="images/gallery-8.png" alt="Category breakdown pie chart showing spending distribution" loading="lazy">
                <div class="gallery-item__overlay">
                    <span class="gallery-item__title">Category Analysis</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Video Section -->
<section class="section section--light" aria-labelledby="videos-title">
    <div class="container">
        <header class="text-center" style="margin-bottom: var(--spacing-xl)">
            <h2 id="videos-title">Video Tutorials</h2>
            <p style="max-width: 600px; margin: 0 auto; color: var(--medium-gray);">
                Watch these videos to learn how to get the most out of Smart Expense Tracker.
            </p>
        </header>
        
        <div style="max-width: 800px; margin: 0 auto;">
            <div class="animate-on-scroll">
                <video controls style="width: 100%; border-radius: var(--radius-md); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);" poster="images/video-thumb-1.svg">
                    <source src="videos/Smart_Expense_Tracker_Video_Generation.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div style="text-align: center; margin-top: var(--spacing-md);">
                    <h3 style="color: var(--primary-color); margin-bottom: var(--spacing-sm);">Smart Expense Tracker Demo</h3>
                    <p style="color: var(--medium-gray);">Watch how our app helps you manage your finances effortlessly</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta" aria-labelledby="cta-title">
    <div class="container">
        <h2 id="cta-title" class="cta__title">Ready to See It in Action?</h2>
        <p class="cta__text">
            Start your free trial today and experience the power of smart expense tracking firsthand.
        </p>
        <div class="cta__buttons">
            <a href="<?php echo SITE_URL; ?>/pricing.php" class="btn btn--white btn--lg">Start Free Trial</a>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn--outline-white btn--lg">Request Demo</a>
        </div>
    </div>
</section>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Image lightbox">
    <button class="lightbox__close" aria-label="Close lightbox">✕</button>
    <button class="lightbox__nav lightbox__nav--prev" aria-label="Previous image">❮</button>
    <div class="lightbox__content">
        <img src="" alt="" id="lightbox-image">
    </div>
    <button class="lightbox__nav lightbox__nav--next" aria-label="Next image">❯</button>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
