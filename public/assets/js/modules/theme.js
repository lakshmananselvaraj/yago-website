/* =========================================================================
   Vipasa Yoga — Theme toggle (light/dark), backed by localStorage.
   ========================================================================= */

const STORAGE_KEY = 'vipasa-theme';

function applyTheme(theme) {
  if (theme === 'light' || theme === 'dark') {
    document.documentElement.setAttribute('data-theme', theme);
  } else {
    document.documentElement.removeAttribute('data-theme');
  }
}

function syncToggles(theme) {
  const isDark = theme === 'dark';
  document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
    toggle.setAttribute('aria-pressed', String(isDark));
  });
}

export function initTheme() {
  const saved = localStorage.getItem(STORAGE_KEY);
  if (saved === 'light' || saved === 'dark') {
    applyTheme(saved);
  }

  const wireToggles = () => {
    syncToggles(saved === 'light' || saved === 'dark' ? saved : document.documentElement.getAttribute('data-theme'));
    document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
      toggle.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const isCurrentlyDark = current
          ? current === 'dark'
          : window.matchMedia('(prefers-color-scheme: dark)').matches;
        const next = isCurrentlyDark ? 'light' : 'dark';
        applyTheme(next);
        localStorage.setItem(STORAGE_KEY, next);
        syncToggles(next);
      });
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', wireToggles);
  } else {
    wireToggles();
  }
}
