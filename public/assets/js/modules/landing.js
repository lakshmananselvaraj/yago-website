/* =========================================================================
   Vipasa Yoga — Landing page controller
   Page-specific behavior for the public marketing landing page. Imported
   only by that page's own inline <script type="module">, not by app.js.
   ========================================================================= */

import { apiPost } from './api.js';
import toast from './toast.js';
import { showFieldError, clearFieldError } from './validation.js';

const SCROLL_THRESHOLD = 60;
const COUNTER_DURATION = 1200;
const SLIDE_INTERVAL = 5000;

function prefersReducedMotion() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/* -------------------------------------------------------------------------
   1. Sticky nav scroll state
   ------------------------------------------------------------------------- */
function initStickyNav() {
  const nav = document.querySelector('.nav-glass');
  if (!nav) return;

  let ticking = false;
  const update = () => {
    nav.classList.toggle('nav-glass--scrolled', window.scrollY > SCROLL_THRESHOLD);
    ticking = false;
  };

  window.addEventListener('scroll', () => {
    if (!ticking) {
      requestAnimationFrame(update);
      ticking = true;
    }
  }, { passive: true });

  update();

  // app.js already toggles .is-open on [data-nav-toggle] click; just keep
  // aria-expanded in sync for accessibility.
  nav.querySelectorAll('[data-nav-toggle]').forEach((toggleBtn) => {
    toggleBtn.addEventListener('click', () => {
      toggleBtn.setAttribute('aria-expanded', String(nav.classList.contains('is-open')));
    });
  });
}

/* -------------------------------------------------------------------------
   2. Smooth scroll for in-page anchor links
   ------------------------------------------------------------------------- */
function initSmoothScroll() {
  if (prefersReducedMotion()) return;

  document.querySelectorAll('a[href^="#"]').forEach((link) => {
    const targetId = link.getAttribute('href').slice(1);
    if (!targetId) return;
    const target = document.getElementById(targetId);
    if (!target) return;

    link.addEventListener('click', (event) => {
      event.preventDefault();
      target.scrollIntoView({ behavior: 'smooth' });
    });
  });
}

/* -------------------------------------------------------------------------
   3. Mouse parallax on the hero illustration layers
   ------------------------------------------------------------------------- */
function initHeroParallax() {
  const hero = document.querySelector('.hero');
  const layers = document.querySelectorAll('.hero__parallax-layer');
  if (!hero || !layers.length || prefersReducedMotion()) return;

  hero.addEventListener('mousemove', (event) => {
    const rect = hero.getBoundingClientRect();
    const relX = (event.clientX - rect.left) / rect.width - 0.5;
    const relY = (event.clientY - rect.top) / rect.height - 0.5;

    layers.forEach((layer) => {
      const depth = Number(layer.dataset.parallaxDepth) || 20;
      const x = relX * depth;
      const y = relY * depth;
      layer.style.transform = `translate(${x.toFixed(1)}px, ${y.toFixed(1)}px)`;
    });
  });

  hero.addEventListener('mouseleave', () => {
    layers.forEach((layer) => {
      layer.style.transform = 'translate(0, 0)';
    });
  });
}

/* -------------------------------------------------------------------------
   4. Animated stat counters
   ------------------------------------------------------------------------- */
function animateCounter(el) {
  const target = Number(el.dataset.countTo) || 0;

  if (prefersReducedMotion()) {
    el.textContent = target.toLocaleString();
    return;
  }

  const start = performance.now();

  function tick(now) {
    const progress = Math.min((now - start) / COUNTER_DURATION, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const value = Math.round(target * eased);
    el.textContent = value.toLocaleString();

    if (progress < 1) {
      requestAnimationFrame(tick);
    } else {
      el.textContent = target.toLocaleString();
    }
  }

  requestAnimationFrame(tick);
}

function initStatCounters() {
  const values = document.querySelectorAll('.stat-counter__value[data-count-to]');
  if (!values.length) return;

  if (!('IntersectionObserver' in window)) {
    values.forEach(animateCounter);
    return;
  }

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      animateCounter(entry.target);
      obs.unobserve(entry.target);
    });
  }, { threshold: 0.4 });

  values.forEach((el) => observer.observe(el));
}

/* -------------------------------------------------------------------------
   5. Testimonials slider
   ------------------------------------------------------------------------- */
function initTestimonialSlider() {
  const carousel = document.querySelector('.testimonial-carousel');
  if (!carousel) return;

  const slides = Array.from(carousel.querySelectorAll('.testimonial-slide'));
  const dots = Array.from(carousel.querySelectorAll('.testimonial-dots__dot'));
  if (!slides.length) return;

  let activeIndex = Math.max(slides.findIndex((slide) => slide.classList.contains('is-active')), 0);
  let timer = null;

  function goTo(index) {
    activeIndex = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => slide.classList.toggle('is-active', i === activeIndex));
    dots.forEach((dot, i) => dot.classList.toggle('is-active', i === activeIndex));
  }

  function start() {
    if (prefersReducedMotion() || slides.length < 2) return;
    stop();
    timer = setInterval(() => goTo(activeIndex + 1), SLIDE_INTERVAL);
  }

  function stop() {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }

  dots.forEach((dot, i) => {
    dot.addEventListener('click', () => {
      goTo(i);
      start();
    });
  });

  carousel.addEventListener('mouseenter', stop);
  carousel.addEventListener('mouseleave', start);

  goTo(activeIndex);
  start();
}

/* -------------------------------------------------------------------------
   6. FAQ accordion
   ------------------------------------------------------------------------- */
function initFaqAccordion() {
  const items = document.querySelectorAll('.faq-item');
  if (!items.length) return;

  items.forEach((item) => {
    const question = item.querySelector('.faq-item__question');
    if (!question) return;

    question.addEventListener('click', () => {
      const isOpen = item.classList.contains('is-open');

      items.forEach((other) => {
        other.classList.remove('is-open');
        other.querySelector('.faq-item__question')?.setAttribute('aria-expanded', 'false');
      });

      if (!isOpen) {
        item.classList.add('is-open');
        question.setAttribute('aria-expanded', 'true');
      }
    });
  });
}

/* -------------------------------------------------------------------------
   7. Contact form
   ------------------------------------------------------------------------- */
function initContactForm() {
  const form = document.getElementById('contact-form');
  if (!form) return;

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const nameEl = form.querySelector('[name="name"]');
    const emailEl = form.querySelector('[name="email"]');
    const messageEl = form.querySelector('[name="message"]');
    [nameEl, emailEl, messageEl].forEach((el) => el && clearFieldError(el));

    const submitBtn = form.querySelector('[type="submit"]');
    submitBtn?.classList.add('is-loading');

    try {
      const res = await apiPost('/api/contact', {
        name: nameEl?.value ?? '',
        email: emailEl?.value ?? '',
        message: messageEl?.value ?? '',
      });
      toast.success(res?.message ?? 'Thanks for reaching out — we\'ll get back to you soon.');
      form.reset();
    } catch (err) {
      toast.error(err.message ?? 'Something went wrong. Please try again.');
      if (err.errors) {
        Object.entries(err.errors).forEach(([field, messages]) => {
          const el = form.querySelector(`[name="${field}"]`);
          const message = Array.isArray(messages) ? messages[0] : messages;
          if (el && message) showFieldError(el, message);
        });
      }
    } finally {
      submitBtn?.classList.remove('is-loading');
    }
  });
}

/* -------------------------------------------------------------------------
   8. Newsletter form
   ------------------------------------------------------------------------- */
function initNewsletterForm() {
  const form = document.getElementById('newsletter-form');
  if (!form) return;

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const emailEl = form.querySelector('[name="email"]');
    const submitBtn = form.querySelector('[type="submit"]');
    submitBtn?.classList.add('is-loading');

    try {
      const res = await apiPost('/api/newsletter/subscribe', { email: emailEl?.value ?? '' });
      toast.success(res?.message ?? 'You\'re subscribed! Watch your inbox for updates.');
      form.reset();
    } catch (err) {
      toast.error(err.message ?? 'Something went wrong. Please try again.');
    } finally {
      submitBtn?.classList.remove('is-loading');
    }
  });
}

export function initLanding() {
  initStickyNav();
  initSmoothScroll();
  initHeroParallax();
  initStatCounters();
  initTestimonialSlider();
  initFaqAccordion();
  initContactForm();
  initNewsletterForm();
}
