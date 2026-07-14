<?php

use App\Core\View;

$pageTitle = 'Schedule your session — Vipasa Yoga';
$pageCss = 'booking';
$portal = 'client';
$active = 'services';
?>
<div class="booking-page container page-enter">
    <div class="booking-layout">
        <div class="slot-picker">
            <div class="card">
                <div class="card__header">
                    <div>
                        <div class="card__title"><?= View::e($package['name']) ?></div>
                        <div class="card__subtitle">With <?= View::e($instructor['name']) ?> · <?= (int) $package['duration_minutes'] ?> min · <?= (int) $package['sessions_count'] ?> session<?= (int) $package['sessions_count'] > 1 ? 's' : '' ?></div>
                    </div>
                </div>
                <div class="card__body"><?= View::e($package['description'] ?? '') ?></div>
            </div>

            <div>
                <div class="slot-picker__section-title">Select a date</div>
                <div id="calendar-root"></div>
            </div>

            <div id="slots-section" hidden>
                <div class="slot-picker__section-title">Available times</div>
                <div class="time-slot-grid" id="time-slot-grid"></div>
            </div>
        </div>

        <div class="booking-summary">
            <div class="booking-summary__title">Booking summary</div>
            <div class="booking-summary__item">
                <div class="booking-summary__item-image flex items-center justify-center">
                    <?php if (!empty($instructor['avatar_path'])): ?>
                    <img src="<?= View::e($instructor['avatar_path']) ?>" alt="">
                    <?php else: ?>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M12 3c-2 3-2 6 0 9 2-3 2-6 0-9Z"/>
                        <path d="M12 12c-4 2-6 5-6 8a6 6 0 0 0 12 0c0-3-2-6-6-8Z"/>
                    </svg>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="booking-summary__item-name"><?= View::e($instructor['name']) ?></div>
                    <div class="booking-summary__item-meta"><?= View::e($package['name']) ?></div>
                </div>
            </div>

            <div class="booking-summary__item-meta mb-4" id="summary-datetime">Select a date and time to continue.</div>

            <div id="coupon-section" hidden>
                <div class="coupon-row">
                    <input type="text" id="coupon-code" placeholder="Coupon code">
                    <button type="button" class="btn btn-secondary" id="apply-coupon-btn">Apply</button>
                </div>
                <div class="coupon-applied" id="coupon-applied-banner" hidden>
                    <span id="coupon-applied-text"></span>
                    <button type="button" class="btn-link" id="remove-coupon-btn">Remove</button>
                </div>
            </div>

            <table class="price-breakdown" id="price-breakdown" hidden>
                <tbody>
                    <tr><td>Package price</td><td id="row-price"></td></tr>
                    <tr class="is-discount" id="row-discount" hidden><td>Discount</td><td id="row-discount-value"></td></tr>
                    <tr><td>Tax</td><td id="row-tax">Calculated at checkout</td></tr>
                    <tr class="price-breakdown__total"><td>Estimated total</td><td id="row-total"></td></tr>
                </tbody>
            </table>

            <div class="form-group" id="notes-section" hidden>
                <textarea id="notes" class="form-group__control" placeholder=" "></textarea>
                <label class="form-group__label" for="notes">Notes for your instructor (optional)</label>
            </div>

            <div class="booking-summary__cta">
                <button type="button" class="btn btn-accent btn-lg btn-block" id="confirm-booking-btn" disabled>Select a time slot</button>
            </div>
        </div>
    </div>
</div>
<script type="module">
import { apiGet, apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';
import { initCalendarPicker, markDateStates } from '/assets/js/modules/calendarPicker.js';

const instructorId = <?= (int) $instructor['id'] ?>;
const packageId = <?= (int) $package['id'] ?>;
const packagePrice = <?= (float) $package['price'] ?>;
const packageCurrency = <?= json_encode($package['currency']) ?>;

const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

const calendarRoot = document.getElementById('calendar-root');
const slotsSection = document.getElementById('slots-section');
const slotGrid = document.getElementById('time-slot-grid');
const summaryDatetime = document.getElementById('summary-datetime');
const couponSection = document.getElementById('coupon-section');
const couponInput = document.getElementById('coupon-code');
const applyCouponBtn = document.getElementById('apply-coupon-btn');
const couponAppliedBanner = document.getElementById('coupon-applied-banner');
const couponAppliedText = document.getElementById('coupon-applied-text');
const removeCouponBtn = document.getElementById('remove-coupon-btn');
const priceBreakdown = document.getElementById('price-breakdown');
const rowPrice = document.getElementById('row-price');
const rowDiscountRow = document.getElementById('row-discount');
const rowDiscountValue = document.getElementById('row-discount-value');
const rowTotal = document.getElementById('row-total');
const notesSection = document.getElementById('notes-section');
const notesInput = document.getElementById('notes');
const confirmBtn = document.getElementById('confirm-booking-btn');

let selectedDate = null;
let selectedSlot = null;
let appliedCoupon = null;

function money(amount) {
    return packageCurrency + ' ' + Number(amount).toFixed(2);
}

function updatePriceBreakdown() {
    if (!selectedSlot) {
        priceBreakdown.hidden = true;
        return;
    }
    priceBreakdown.hidden = false;
    rowPrice.textContent = money(packagePrice);
    if (appliedCoupon) {
        rowDiscountRow.hidden = false;
        rowDiscountValue.textContent = '-' + money(appliedCoupon.discount_amount);
        rowTotal.textContent = money(packagePrice - appliedCoupon.discount_amount) + ' + tax';
    } else {
        rowDiscountRow.hidden = true;
        rowTotal.textContent = money(packagePrice) + ' + tax';
    }
}

async function loadMonthAvailability() {
    const titleEl = calendarRoot.querySelector('.calendar__title');
    if (!titleEl) return;
    const [monthName, year] = titleEl.textContent.split(' ');
    const month = MONTH_NAMES.indexOf(monthName) + 1;

    try {
        const result = await apiGet(`/api/instructors/${instructorId}/availability?month=${month}&year=${year}`);
        markDateStates(calendarRoot, {
            available: result.data.available,
            unavailable: result.data.unavailable,
            selectedDate,
        });
    } catch (err) {
        toast.error(err.message);
    }
}

function renderSlots(slots) {
    slotGrid.innerHTML = '';
    if (!slots.length) {
        slotGrid.innerHTML = '<p class="text-muted">No available times on this date.</p>';
        return;
    }
    slots.forEach((slot) => {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'time-slot';
        const start = slot.start_time.slice(0, 5);
        const end = slot.end_time.slice(0, 5);
        chip.innerHTML = `<span>${start}</span><span class="time-slot__meta">${end}</span>`;
        chip.addEventListener('click', () => selectSlot(slot, chip));
        slotGrid.appendChild(chip);
    });
}

async function loadSlotsForDate(dateStr) {
    selectedDate = dateStr;
    selectedSlot = null;
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Select a time slot';
    couponSection.hidden = true;
    notesSection.hidden = true;
    priceBreakdown.hidden = true;
    markDateStates(calendarRoot, { selectedDate });
    slotsSection.hidden = false;
    slotGrid.innerHTML = '<div class="skeleton skeleton-text w-60"></div>';

    try {
        const result = await apiGet(`/api/instructors/${instructorId}/availability?date=${dateStr}&package_id=${packageId}`);
        renderSlots(result.data.slots);
    } catch (err) {
        slotGrid.innerHTML = '';
        toast.error(err.message);
    }
}

function selectSlot(slot, chipEl) {
    selectedSlot = slot;
    slotGrid.querySelectorAll('.time-slot').forEach((el) => el.classList.remove('is-selected'));
    chipEl.classList.add('is-selected');
    summaryDatetime.textContent = `${selectedDate} at ${slot.start_time.slice(0, 5)}`;
    couponSection.hidden = false;
    notesSection.hidden = false;
    confirmBtn.disabled = false;
    confirmBtn.textContent = 'Confirm booking';
    updatePriceBreakdown();
}

applyCouponBtn.addEventListener('click', async () => {
    const code = couponInput.value.trim();
    if (!code) return;
    applyCouponBtn.classList.add('is-loading');
    applyCouponBtn.disabled = true;

    try {
        const result = await apiPost('/api/coupons/validate', { code, subtotal: packagePrice });
        appliedCoupon = result.data;
        couponAppliedText.textContent = `${appliedCoupon.code} applied — -${money(appliedCoupon.discount_amount)}`;
        couponAppliedBanner.hidden = false;
        updatePriceBreakdown();
        toast.success('Coupon applied.');
    } catch (err) {
        toast.error(err.message);
    } finally {
        applyCouponBtn.classList.remove('is-loading');
        applyCouponBtn.disabled = false;
    }
});

removeCouponBtn.addEventListener('click', () => {
    appliedCoupon = null;
    couponInput.value = '';
    couponAppliedBanner.hidden = true;
    updatePriceBreakdown();
});

confirmBtn.addEventListener('click', async () => {
    if (!selectedSlot) return;
    confirmBtn.classList.add('is-loading');
    confirmBtn.disabled = true;

    try {
        const result = await apiPost('/api/bookings', {
            instructor_id: instructorId,
            package_id: packageId,
            slot_date: selectedDate,
            start_time: selectedSlot.start_time,
            coupon_code: appliedCoupon ? appliedCoupon.code : '',
            notes: notesInput.value.trim(),
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        });
        window.location.href = result.data.redirect;
    } catch (err) {
        toast.error(err.message);
        confirmBtn.classList.remove('is-loading');
        confirmBtn.disabled = false;
        if (err.status === 409) {
            loadSlotsForDate(selectedDate);
        }
    }
});

initCalendarPicker(calendarRoot, {
    instructorId,
    onDateSelect: loadSlotsForDate,
});
calendarRoot.addEventListener('click', (event) => {
    if (event.target.closest('.calendar__nav-btn')) {
        loadMonthAvailability();
    }
});
loadMonthAvailability();
</script>
