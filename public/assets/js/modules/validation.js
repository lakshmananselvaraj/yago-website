/* =========================================================================
   Vipasa Yoga — Field-level validation error helpers
   ========================================================================= */

function getFormGroup(inputEl) {
  return inputEl.closest('.form-group');
}

function getOrCreateErrorEl(formGroup) {
  let errorEl = formGroup.querySelector('.form-group__error');
  if (!errorEl) {
    errorEl = document.createElement('div');
    errorEl.className = 'form-group__error';
    formGroup.appendChild(errorEl);
  }
  return errorEl;
}

export function showFieldError(inputEl, message) {
  const formGroup = getFormGroup(inputEl);
  if (!formGroup) return;
  formGroup.classList.add('has-error');
  const errorEl = getOrCreateErrorEl(formGroup);
  errorEl.textContent = message;
}

export function clearFieldError(inputEl) {
  const formGroup = getFormGroup(inputEl);
  if (!formGroup) return;
  formGroup.classList.remove('has-error');
  const errorEl = formGroup.querySelector('.form-group__error');
  if (errorEl) {
    errorEl.textContent = '';
  }
}
