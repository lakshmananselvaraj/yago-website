/* =========================================================================
   Vipasa Yoga — Toast notifications
   ========================================================================= */

const ICONS = {
  success: '✓',
  error: '✕',
  warning: '!',
  info: 'i',
};

const LEAVE_DURATION = 300;

function getContainer() {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  return container;
}

function removeToast(toastEl) {
  if (!toastEl.isConnected) return;
  toastEl.classList.add('is-leaving');
  setTimeout(() => toastEl.remove(), LEAVE_DURATION);
}

export function showToast({ type = 'info', title, message, duration = 4500 } = {}) {
  const container = getContainer();

  const toastEl = document.createElement('div');
  toastEl.className = `toast toast-${type}`;
  toastEl.setAttribute('role', 'status');

  const iconEl = document.createElement('div');
  iconEl.className = 'toast__icon';
  iconEl.textContent = ICONS[type] ?? ICONS.info;

  const bodyEl = document.createElement('div');
  bodyEl.className = 'toast__body';

  if (title) {
    const titleEl = document.createElement('div');
    titleEl.className = 'toast__title';
    titleEl.textContent = title;
    bodyEl.appendChild(titleEl);
  }

  if (message) {
    const messageEl = document.createElement('div');
    messageEl.className = 'toast__message';
    messageEl.textContent = message;
    bodyEl.appendChild(messageEl);
  }

  const closeEl = document.createElement('button');
  closeEl.type = 'button';
  closeEl.className = 'toast__close';
  closeEl.setAttribute('aria-label', 'Dismiss notification');
  closeEl.innerHTML = '&times;';
  closeEl.addEventListener('click', () => removeToast(toastEl));

  toastEl.append(iconEl, bodyEl, closeEl);
  container.appendChild(toastEl);

  if (duration > 0) {
    setTimeout(() => removeToast(toastEl), duration);
  }

  return toastEl;
}

const toast = {
  success: (message, title = 'Success') => showToast({ type: 'success', title, message }),
  error: (message, title = 'Error') => showToast({ type: 'error', title, message }),
  warning: (message, title = 'Warning') => showToast({ type: 'warning', title, message }),
  info: (message, title = 'Info') => showToast({ type: 'info', title, message }),
};

export default toast;
