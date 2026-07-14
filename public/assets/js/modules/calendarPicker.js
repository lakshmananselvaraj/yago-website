/* =========================================================================
   Vipasa Yoga — Booking date/time calendar picker
   Renders a month grid; caller supplies availability via markDateStates().
   Date strings use ISO 'YYYY-MM-DD' format throughout.
   ========================================================================= */

const WEEKDAYS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
const MONTH_NAMES = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
];

const stateByRoot = new WeakMap();

function pad2(n) {
  return String(n).padStart(2, '0');
}

function toDateString(year, month, day) {
  return `${year}-${pad2(month + 1)}-${pad2(day)}`;
}

function render(root, state) {
  root.innerHTML = '';

  const calendarEl = document.createElement('div');
  calendarEl.className = 'calendar';

  const header = document.createElement('div');
  header.className = 'calendar__header';

  const title = document.createElement('div');
  title.className = 'calendar__title';
  title.textContent = `${MONTH_NAMES[state.month]} ${state.year}`;

  const nav = document.createElement('div');
  nav.className = 'calendar__nav';

  const prevBtn = document.createElement('button');
  prevBtn.type = 'button';
  prevBtn.className = 'calendar__nav-btn';
  prevBtn.setAttribute('aria-label', 'Previous month');
  prevBtn.textContent = '‹';
  prevBtn.addEventListener('click', () => {
    state.month -= 1;
    if (state.month < 0) {
      state.month = 11;
      state.year -= 1;
    }
    render(root, state);
  });

  const nextBtn = document.createElement('button');
  nextBtn.type = 'button';
  nextBtn.className = 'calendar__nav-btn';
  nextBtn.setAttribute('aria-label', 'Next month');
  nextBtn.textContent = '›';
  nextBtn.addEventListener('click', () => {
    state.month += 1;
    if (state.month > 11) {
      state.month = 0;
      state.year += 1;
    }
    render(root, state);
  });

  nav.append(prevBtn, nextBtn);
  header.append(title, nav);

  const weekdaysEl = document.createElement('div');
  weekdaysEl.className = 'calendar__weekdays';
  WEEKDAYS.forEach((wd) => {
    const wdEl = document.createElement('div');
    wdEl.className = 'calendar__weekday';
    wdEl.textContent = wd;
    weekdaysEl.appendChild(wdEl);
  });

  const gridEl = document.createElement('div');
  gridEl.className = 'calendar__grid';

  const firstOfMonth = new Date(state.year, state.month, 1);
  const startOffset = firstOfMonth.getDay();
  const daysInMonth = new Date(state.year, state.month + 1, 0).getDate();
  const daysInPrevMonth = new Date(state.year, state.month, 0).getDate();

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const totalCells = Math.ceil((startOffset + daysInMonth) / 7) * 7;

  for (let i = 0; i < totalCells; i += 1) {
    const dayNum = i - startOffset + 1;

    let cellYear = state.year;
    let cellMonth = state.month;
    let cellDay = dayNum;
    let isOutside = false;

    if (dayNum < 1) {
      cellDay = daysInPrevMonth + dayNum;
      cellMonth = state.month - 1;
      cellYear = state.year;
      if (cellMonth < 0) {
        cellMonth = 11;
        cellYear -= 1;
      }
      isOutside = true;
    } else if (dayNum > daysInMonth) {
      cellDay = dayNum - daysInMonth;
      cellMonth = state.month + 1;
      cellYear = state.year;
      if (cellMonth > 11) {
        cellMonth = 0;
        cellYear += 1;
      }
      isOutside = true;
    }

    const cellDate = new Date(cellYear, cellMonth, cellDay);
    cellDate.setHours(0, 0, 0, 0);
    const dateStr = toDateString(cellYear, cellMonth, cellDay);

    const dayEl = document.createElement('div');
    dayEl.className = 'calendar__day';
    dayEl.textContent = String(cellDay);
    dayEl.dataset.date = dateStr;

    if (isOutside) dayEl.classList.add('is-outside');
    if (cellDate.getTime() === today.getTime()) dayEl.classList.add('is-today');

    const isPast = cellDate.getTime() < today.getTime();
    if (isPast) {
      dayEl.classList.add('is-past');
    } else {
      dayEl.addEventListener('click', () => {
        if (dayEl.classList.contains('is-unavailable')) return;
        state.onDateSelect?.(dateStr);
      });
    }

    gridEl.appendChild(dayEl);
  }

  calendarEl.append(header, weekdaysEl, gridEl);
  root.appendChild(calendarEl);
}

export function initCalendarPicker(root, { instructorId, onDateSelect } = {}) {
  if (!root) return;
  const now = new Date();
  const state = {
    year: now.getFullYear(),
    month: now.getMonth(),
    instructorId,
    onDateSelect,
  };
  stateByRoot.set(root, state);
  render(root, state);
}

export function markDateStates(root, { available = [], unavailable = [], selectedDate = null } = {}) {
  if (!root) return;
  const availableSet = new Set(available);
  const unavailableSet = new Set(unavailable);

  root.querySelectorAll('.calendar__day').forEach((dayEl) => {
    const dateStr = dayEl.dataset.date;
    dayEl.classList.remove('is-available', 'is-unavailable', 'is-selected');
    if (availableSet.has(dateStr)) dayEl.classList.add('is-available');
    if (unavailableSet.has(dateStr)) dayEl.classList.add('is-unavailable');
    if (selectedDate && dateStr === selectedDate) dayEl.classList.add('is-selected');
  });
}
