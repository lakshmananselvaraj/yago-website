/* =========================================================================
   Vipasa Yoga — Scroll reveal
   Generic, reusable "fade/slide in on scroll" for any element carrying a
   data-reveal attribute (see base.css for the CSS half of this). One-shot:
   once an element has revealed, it's unobserved so it never re-hides on
   scroll-away. No-op (via CSS) for prefers-reduced-motion users.
   ========================================================================= */

export function initScrollReveal(root = document) {
    const targets = root.querySelectorAll('[data-reveal]');

    if (!targets.length) return;

    if (!('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    obs.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -8% 0px' }
    );

    targets.forEach((el) => observer.observe(el));
}
