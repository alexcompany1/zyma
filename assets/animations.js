(function () {
  'use strict';

  document.documentElement.classList.add('js-animations-ready');

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function addClass(el, className) {
    if (el && !el.classList.contains(className)) el.classList.add(className);
  }

  function initAutoAnimations() {
    var page = (window.location.pathname.split('/').pop() || 'index.php').replace('.php', '').replace(/[^a-z0-9_-]/gi, '-');
    document.body.classList.add('page-' + page);
    addClass(document.body, 'page-enter');

    document.querySelectorAll('.landing-header, header.landing-header').forEach(function (el) {
      addClass(el, 'header-animated');
    });

    document.querySelectorAll([
      '.hero',
      '.landing-hero-section',
      '.landing-hero-card',
      '.landing-hero-visual',
      '.landing-section',
      '.main-content',
      '.legal-main-card',
      '.profile-page > .profile-hero',
      '.user-home-hero',
      '.user-home-copy',
      '.user-home-spotlight',
      '.user-home-links',
      '.user-home-section',
      '.cart-header',
      '.profile-card',
      '.section-card',
      'form'
    ].join(',')).forEach(function (el, index) {
      if (!el.classList.contains('reveal') && !el.classList.contains('reveal-left') && !el.classList.contains('reveal-right') && !el.classList.contains('reveal-up')) {
        addClass(el, 'reveal-up');
      }
      el.setAttribute('data-delay', el.getAttribute('data-delay') || Math.min(index * 45, 240));
    });

    document.querySelectorAll([
      '.page-index .landing-hero-card',
      '.page-index .landing-hero-visual',
      '.page-index .landing-section',
      '.page-login form',
      '.page-registro form',
      '.page-forgot_password form',
      '.page-reset_password form'
    ].join(',')).forEach(function (el, index) {
      addClass(el, 'reveal-up');
      el.setAttribute('data-delay', el.getAttribute('data-delay') || Math.min(index * 70, 260));
    });

    document.querySelectorAll([
      '.grid',
      '.grid-products',
      '.landing-featured-grid',
      '.landing-steps-grid',
      '.reviews-track',
      '.stats-visual-grid',
      '.ingredient-visual-grid',
      '.products-ratings-grid',
      '.support-orders-grid',
      '.user-home-stats',
      '.user-home-shortcuts',
      '.user-home-actions',
      '.user-link-grid',
      '.user-home-links',
      '.user-featured-grid',
      '.user-reviews-grid'
    ].join(',')).forEach(function (el) {
      addClass(el, 'reveal-stagger');
    });

    document.querySelectorAll([
      '.card-product',
      '.landing-featured-card',
      '.landing-step-card',
      '.review-card',
      '.pedido-card',
      '.profile-card',
      '.section-card',
      '.stat-visual-card',
      '.ingredient-visual-card',
      '.product-rating-card',
      '.support-summary-card',
      '.support-order-card',
      '.user-stat-card',
      '.user-link-card',
      '.user-home-shortcuts a',
      '.user-home-actions a',
      '.user-home-inline-link',
      '.user-featured-card',
      '.payment-option'
    ].join(',')).forEach(function (el, index) {
      addClass(el, 'interactive-card');
      addClass(el, 'hover-lift');
      if (!el.classList.contains('reveal') && !el.closest('.reveal-stagger')) {
        addClass(el, 'reveal-scale');
        el.setAttribute('data-delay', el.getAttribute('data-delay') || Math.min(index * 35, 220));
      }
    });

    document.querySelectorAll('button, .btn, .landing-cta, .landing-link, .landing-hero-btn, .btn-volver-panel, input[type="submit"], .cart-btn, .profile-btn, .quick-menu-btn').forEach(function (el) {
      addClass(el, 'btn-ripple');
    });

    document.querySelectorAll('table').forEach(function (table) {
      addClass(table, 'table-animated');
      table.querySelectorAll('tbody tr').forEach(function (row, index) {
        row.style.setProperty('--row-delay', Math.min(index * 35, 420) + 'ms');
      });
    });

    document.querySelectorAll('.card-product img, .landing-featured-img img, .user-featured-card img').forEach(function (img) {
      addClass(img, 'media-animated');
    });

    document.querySelectorAll('.user-home-copy, .user-home-spotlight, .user-link-card-star').forEach(function (el) {
      addClass(el, 'user-home-hover-focus');
    });
  }

  function initScrollReveal() {
    var targets = '.reveal, .reveal-up, .reveal-left, .reveal-right, .reveal-scale, .reveal-fade, .reveal-stagger';
    var els = document.querySelectorAll(targets);
    if (els.length === 0) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        var delay = parseInt(el.getAttribute('data-delay') || '0', 10);
        setTimeout(function () {
          el.classList.add('revealed');
          if (el.classList.contains('reveal-stagger')) {
            Array.prototype.forEach.call(el.children, function (child, index) {
              child.style.animationDelay = Math.min(index * 80, 560) + 'ms';
            });
          }
        }, delay);
        observer.unobserve(el);
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    els.forEach(function (el) { observer.observe(el); });
  }

  function initCounters() {
    document.querySelectorAll('.count-up').forEach(function (el) {
      var target = parseFloat(el.getAttribute('data-target') || el.textContent.replace(/[^0-9.]/g, ''));
      var suffix = el.getAttribute('data-suffix') || '';
      var prefix = el.getAttribute('data-prefix') || '';
      var duration = parseInt(el.getAttribute('data-duration') || '1500', 10);
      var isDecimal = target % 1 !== 0;
      var start = performance.now();

      function update(now) {
        var progress = Math.min((now - start) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        var val = eased * target;
        el.textContent = prefix + (isDecimal ? val.toFixed(1) : Math.round(val)) + suffix;
        if (progress < 1) requestAnimationFrame(update);
        else el.textContent = prefix + target + suffix;
      }
      requestAnimationFrame(update);
    });
  }

  function initRipple() {
    document.querySelectorAll('.btn-ripple').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        if (reduceMotion) return;
        var rect = btn.getBoundingClientRect();
        var ripple = document.createElement('span');
        ripple.className = 'ripple-effect';
        var size = Math.max(rect.width, rect.height);
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
        btn.appendChild(ripple);
        setTimeout(function () { if (ripple.parentNode) ripple.parentNode.removeChild(ripple); }, 600);
      });
    });
  }

  function initHeaderScroll() {
    var header = document.querySelector('.landing-header');
    if (!header) return;
    var onScroll = function () {
      header.classList.toggle('header-scrolled', window.scrollY > 12);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  function initTiltCards() {
    if (reduceMotion || !window.matchMedia('(hover: hover)').matches) return;
    document.querySelectorAll('.interactive-card').forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var rect = card.getBoundingClientRect();
        var x = (e.clientX - rect.left) / rect.width - 0.5;
        var y = (e.clientY - rect.top) / rect.height - 0.5;
        card.style.setProperty('--tilt-x', (-y * 3).toFixed(2) + 'deg');
        card.style.setProperty('--tilt-y', (x * 3).toFixed(2) + 'deg');
      });
      card.addEventListener('mouseleave', function () {
        card.style.removeProperty('--tilt-x');
        card.style.removeProperty('--tilt-y');
      });
    });
  }

  function initPageTransitions() {
    if (reduceMotion) return;
    document.querySelectorAll('a[href]').forEach(function (link) {
      var href = link.getAttribute('href');
      if (!href || href.charAt(0) === '#' || link.target === '_blank' || href.indexOf('javascript:') === 0) return;
      link.addEventListener('click', function () {
        document.body.classList.add('page-leaving');
      });
    });
  }

  function init() {
    initAutoAnimations();
    initScrollReveal();
    initCounters();
    initRipple();
    initHeaderScroll();
    initTiltCards();
    initPageTransitions();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
