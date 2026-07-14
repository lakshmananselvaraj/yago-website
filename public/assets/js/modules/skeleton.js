/* =========================================================================
   Vipasa Yoga — Skeleton loading placeholders
   ========================================================================= */

export function showSkeleton(container, variant = 'card', count = 3) {
  if (!container) return;
  container.innerHTML = '';
  for (let i = 0; i < count; i += 1) {
    const el = document.createElement('div');
    el.className = `skeleton skeleton-${variant}`;
    container.appendChild(el);
  }
}

export function clearSkeleton(container) {
  if (!container) return;
  container.innerHTML = '';
}
