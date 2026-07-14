/* =========================================================================
   Vipasa Yoga — Button ripple micro-interaction
   Single delegated listener (not one per button) so it works on every
   .btn across every page, including ones inserted after page load.
   ========================================================================= */

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export function initRipple() {
  if (reduceMotion) return;

  document.addEventListener('pointerdown', (event) => {
    const btn = event.target.closest('.btn');
    if (!btn || btn.disabled || btn.classList.contains('is-disabled')) return;

    const rect = btn.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height) * 2;
    const ripple = document.createElement('span');
    ripple.className = 'btn__ripple';
    ripple.style.width = ripple.style.height = `${size}px`;
    ripple.style.left = `${event.clientX - rect.left - size / 2}px`;
    ripple.style.top = `${event.clientY - rect.top - size / 2}px`;

    btn.appendChild(ripple);
    ripple.addEventListener('animationend', () => ripple.remove());
  });
}
