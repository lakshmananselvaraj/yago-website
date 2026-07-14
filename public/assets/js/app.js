/* =========================================================================
   Vipasa Yoga — App bootstrap
   Runs theme init immediately (avoids flash of wrong theme), then wires up
   truly global chrome (mobile nav toggle) once the DOM is ready.
   Page-specific behavior lives in per-page controller scripts, not here.
   ========================================================================= */

import { initTheme } from './modules/theme.js';
import { initScrollReveal } from './modules/scrollReveal.js';
import { initRipple } from './modules/ripple.js';

initTheme();
initRipple();

function wireMobileNavToggle() {
  document.querySelectorAll('[data-nav-toggle]').forEach((toggleBtn) => {
    toggleBtn.addEventListener('click', () => {
      const nav = toggleBtn.closest('.nav-glass') ?? document.querySelector('.nav-glass');
      nav?.classList.toggle('is-open');
    });
  });
}

function wireProgramCardExpand() {
  document.querySelectorAll('[data-program-more]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.program-card-v2');
      if (!card) return;
      const expanded = card.classList.toggle('is-expanded');
      btn.textContent = expanded ? 'Read Less' : 'Read More';
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  wireMobileNavToggle();
  wireProgramCardExpand();
  initScrollReveal();
});
