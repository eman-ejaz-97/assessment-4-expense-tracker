// Smart Expense Tracking System - Student ID: 20034038

document.addEventListener('DOMContentLoaded', function () {
  initMobileNav();
  initFAQAccordion();
  initPricingToggle();
  initGalleryFilters();
  initLightbox();
  initCarousel();
  initContactForm();
  initScrollAnimations();
});

function initMobileNav() {
  var menuToggle = document.querySelector('.menu-toggle');
  var nav = document.querySelector('.nav');

  if (!menuToggle || !nav) return;

  menuToggle.addEventListener('click', function () {
    menuToggle.classList.toggle('active');
    nav.classList.toggle('active');
  });

  var navLinks = nav.querySelectorAll('.nav__link');
  for (var i = 0; i < navLinks.length; i++) {
    navLinks[i].addEventListener('click', function () {
      menuToggle.classList.remove('active');
      nav.classList.remove('active');
    });
  }
}

function initFAQAccordion() {
  var faqItems = document.querySelectorAll('.faq-item');

  if (faqItems.length === 0) return;

  for (var i = 0; i < faqItems.length; i++) {
    var question = faqItems[i].querySelector('.faq-question');

    if (question) {
      question.addEventListener('click', function () {
        var item = this.parentElement;

        for (var j = 0; j < faqItems.length; j++) {
          if (faqItems[j] !== item) {
            faqItems[j].classList.remove('active');
          }
        }

        item.classList.toggle('active');
      });
    }
  }
}

function initPricingToggle() {
  var toggle = document.getElementById('billing-toggle');
  var monthlyLabel = document.getElementById('monthly-label');
  var yearlyLabel = document.getElementById('yearly-label');
  var priceValues = document.querySelectorAll('.price-value');

  if (!toggle) return;

  var prices = {
    monthly: [9, 29, 79],
    yearly: [7, 23, 63],
  };

  toggle.addEventListener('click', function () {
    var isYearly = toggle.classList.toggle('active');

    if (monthlyLabel && yearlyLabel) {
      if (isYearly) {
        monthlyLabel.classList.remove('active');
        yearlyLabel.classList.add('active');
      } else {
        monthlyLabel.classList.add('active');
        yearlyLabel.classList.remove('active');
      }
    }

    for (var i = 0; i < priceValues.length; i++) {
      if (isYearly) {
        priceValues[i].textContent = prices.yearly[i];
      } else {
        priceValues[i].textContent = prices.monthly[i];
      }
    }
  });
}

function initGalleryFilters() {
  var filters = document.querySelectorAll('.gallery-filter');
  var items = document.querySelectorAll('.gallery-item');

  if (filters.length === 0) return;

  for (var i = 0; i < filters.length; i++) {
    filters[i].addEventListener('click', function () {
      var filterValue = this.getAttribute('data-filter');

      for (var j = 0; j < filters.length; j++) {
        filters[j].classList.remove('active');
      }
      this.classList.add('active');

      for (var k = 0; k < items.length; k++) {
        var category = items[k].getAttribute('data-category');

        if (filterValue === 'all' || category === filterValue) {
          items[k].style.display = '';
        } else {
          items[k].style.display = 'none';
        }
      }
    });
  }
}

function initLightbox() {
  var lightbox = document.getElementById('lightbox');
  var lightboxImg = document.getElementById('lightbox-image');
  var galleryItems = document.querySelectorAll('.gallery-item');

  if (!lightbox || !lightboxImg || galleryItems.length === 0) return;

  var closeBtn = lightbox.querySelector('.lightbox__close');
  var prevBtn = lightbox.querySelector('.lightbox__nav--prev');
  var nextBtn = lightbox.querySelector('.lightbox__nav--next');

  var images = [];
  var currentIndex = 0;

  for (var i = 0; i < galleryItems.length; i++) {
    var img = galleryItems[i].querySelector('img');
    if (img) {
      images.push(img.src);

      (function (index) {
        galleryItems[index].addEventListener('click', function () {
          currentIndex = index;
          openLightbox();
        });
      })(i);
    }
  }

  function openLightbox() {
    lightboxImg.src = images[currentIndex];
    lightbox.classList.add('active');
  }

  function closeLightbox() {
    lightbox.classList.remove('active');
  }

  function showPrev() {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    lightboxImg.src = images[currentIndex];
  }

  function showNext() {
    currentIndex = (currentIndex + 1) % images.length;
    lightboxImg.src = images[currentIndex];
  }

  if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
  if (prevBtn) prevBtn.addEventListener('click', showPrev);
  if (nextBtn) nextBtn.addEventListener('click', showNext);

  lightbox.addEventListener('click', function (e) {
    if (e.target === lightbox) closeLightbox();
  });

  document.addEventListener('keydown', function (e) {
    if (!lightbox.classList.contains('active')) return;

    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') showPrev();
    if (e.key === 'ArrowRight') showNext();
  });
}

function initCarousel() {
  var carousel = document.querySelector('.carousel');
  if (!carousel) return;

  var track = carousel.querySelector('.carousel__track');
  var slides = carousel.querySelectorAll('.carousel__slide');
  var prevBtn = carousel.querySelector('.carousel__nav--prev');
  var nextBtn = carousel.querySelector('.carousel__nav--next');
  var dots = carousel.querySelectorAll('.carousel__dot');

  if (!track || slides.length === 0) return;

  var currentSlide = 0;
  var autoPlayTimer;

  function goToSlide(index) {
    currentSlide = index;

    if (currentSlide < 0) currentSlide = slides.length - 1;
    if (currentSlide >= slides.length) currentSlide = 0;

    track.style.transform = 'translateX(-' + currentSlide * 100 + '%)';

    for (var i = 0; i < dots.length; i++) {
      if (i === currentSlide) {
        dots[i].classList.add('active');
      } else {
        dots[i].classList.remove('active');
      }
    }
  }

  function nextSlide() {
    goToSlide(currentSlide + 1);
  }

  function prevSlide() {
    goToSlide(currentSlide - 1);
  }

  function startAutoPlay() {
    autoPlayTimer = setInterval(nextSlide, 4000);
  }

  function stopAutoPlay() {
    clearInterval(autoPlayTimer);
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', function () {
      prevSlide();
      stopAutoPlay();
      startAutoPlay();
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', function () {
      nextSlide();
      stopAutoPlay();
      startAutoPlay();
    });
  }

  for (var i = 0; i < dots.length; i++) {
    (function (index) {
      dots[index].addEventListener('click', function () {
        goToSlide(index);
        stopAutoPlay();
        startAutoPlay();
      });
    })(i);
  }

  goToSlide(0);
  startAutoPlay();
}

function initContactForm() {
  var form = document.getElementById('contact-form');
  if (!form) return;

  var nameInput = document.getElementById('name');
  var emailInput = document.getElementById('email');
  var phoneInput = document.getElementById('phone');
  var subjectInput = document.getElementById('subject');
  var messageInput = document.getElementById('message');

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    var isValid = true;

    if (!nameInput.value.trim()) {
      showError('name', 'Please enter your name');
      isValid = false;
    } else if (nameInput.value.trim().length < 2) {
      showError('name', 'Name must be at least 2 characters');
      isValid = false;
    } else {
      clearError('name');
    }

    if (!emailInput.value.trim()) {
      showError('email', 'Please enter your email');
      isValid = false;
    } else if (!isValidEmail(emailInput.value)) {
      showError('email', 'Please enter a valid email');
      isValid = false;
    } else {
      clearError('email');
    }

    if (phoneInput.value.trim() && !isValidPhone(phoneInput.value)) {
      showError('phone', 'Please enter a valid phone number');
      isValid = false;
    } else {
      clearError('phone');
    }

    if (!subjectInput.value) {
      showError('subject', 'Please select a subject');
      isValid = false;
    } else {
      clearError('subject');
    }

    if (!messageInput.value.trim()) {
      showError('message', 'Please enter your message');
      isValid = false;
    } else if (messageInput.value.trim().length < 10) {
      showError('message', 'Message must be at least 10 characters');
      isValid = false;
    } else {
      clearError('message');
    }

    if (isValid) {
      // Actually submit the form to the server
      form.submit();
    }
  });

  if (nameInput) {
    nameInput.addEventListener('blur', function () {
      if (!this.value.trim()) {
        showError('name', 'Please enter your name');
      } else {
        clearError('name');
      }
    });
  }

  if (emailInput) {
    emailInput.addEventListener('blur', function () {
      if (this.value.trim() && !isValidEmail(this.value)) {
        showError('email', 'Please enter a valid email');
      } else if (this.value.trim()) {
        clearError('email');
      }
    });
  }

  function showError(fieldId, message) {
    var input = document.getElementById(fieldId);
    var error = document.getElementById(fieldId + '-error');

    if (input) input.classList.add('error');
    if (error) {
      error.textContent = message;
      error.classList.add('visible');
    }
  }

  function clearError(fieldId) {
    var input = document.getElementById(fieldId);
    var error = document.getElementById(fieldId + '-error');

    if (input) {
      input.classList.remove('error');
      input.classList.add('success');
    }
    if (error) {
      error.textContent = '';
      error.classList.remove('visible');
    }
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function isValidPhone(phone) {
    return (
      /^[\d\s\-\+\(\)]+$/.test(phone) && phone.replace(/\D/g, '').length >= 8
    );
  }
}

function initScrollAnimations() {
  var animatedElements = document.querySelectorAll('.animate-on-scroll');

  if (animatedElements.length === 0) return;

  function checkScroll() {
    var windowHeight = window.innerHeight;

    for (var i = 0; i < animatedElements.length; i++) {
      var element = animatedElements[i];
      var elementTop = element.getBoundingClientRect().top;

      if (elementTop < windowHeight - 100) {
        element.classList.add('visible');
      }
    }
  }

  checkScroll();

  window.addEventListener('scroll', checkScroll);
  window.addEventListener('resize', checkScroll);
}
