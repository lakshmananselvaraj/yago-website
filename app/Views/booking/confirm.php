<?php

use App\Core\View;

$pageTitle = 'Booking Confirmation';
$pageCss = 'booking';
$portal = 'client';
$active = 'services';

$statusBadgeClass = match ($booking['status']) {
    'confirmed' => 'badge-confirmed',
    'completed' => 'badge-completed',
    'cancelled' => 'badge-cancelled',
    default => 'badge-pending',
};

$statusLabel = match ($booking['status']) {
    'confirmed' => 'Confirmed',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
    default => 'Pending payment',
};

$dateLabel = date('l, d M Y', strtotime($booking['slot_date']));
$timeLabel = date('g:i A', strtotime($booking['start_time'])) . ' – ' . date('g:i A', strtotime($booking['end_time']));
$currency = $booking['currency'];
?>
<div class="container booking-page">
    <div class="booking-success">
        <div class="booking-success__icon">
            <?= View::icon('check', 'icon', 28) ?>
        </div>
        <h1>Booking created</h1>
        <p class="text-muted">Reference <strong><?= View::e($booking['booking_ref']) ?></strong></p>
        <span class="badge <?= $statusBadgeClass ?>"><?= $statusLabel ?></span>
    </div>

    <div class="booking-summary" style="max-width:560px;margin-inline:auto;">
        <div class="booking-summary__title">Session details</div>

        <div class="booking-summary__item">
            <div class="booking-summary__item-image">
                <?php if (!empty($instructor['avatar_path'])): ?>
                <img src="<?= View::e($instructor['avatar_path']) ?>" alt="">
                <?php endif; ?>
            </div>
            <div>
                <div class="booking-summary__item-name"><?= View::e($instructor['name']) ?></div>
                <div class="booking-summary__item-meta"><?= View::e($package['name']) ?></div>
                <div class="booking-summary__item-meta"><?= View::e($dateLabel) ?> · <?= View::e($timeLabel) ?> (<?= View::e($booking['client_timezone']) ?>)</div>
            </div>
        </div>

        <table class="price-breakdown">
            <tbody>
                <tr>
                    <td>Package price</td>
                    <td><?= View::e($currency) ?> <?= number_format((float) $booking['price'], 2) ?></td>
                </tr>
                <tr>
                    <td>Tax</td>
                    <td><?= View::e($currency) ?> <?= number_format((float) $booking['tax_amount'], 2) ?></td>
                </tr>
                <?php if ((float) $booking['discount_amount'] > 0): ?>
                <tr class="is-discount">
                    <td>Discount</td>
                    <td>&minus; <?= View::e($currency) ?> <?= number_format((float) $booking['discount_amount'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="price-breakdown__total">
                    <td>Total</td>
                    <td><?= View::e($currency) ?> <?= number_format((float) $booking['total_amount'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <?php if (!empty($booking['notes'])): ?>
        <p class="form-group__hint">Note: <?= View::e($booking['notes']) ?></p>
        <?php endif; ?>

        <?php if ($booking['status'] === 'pending_payment'): ?>
            <?php if (!$paymentConfigured): ?>
            <div class="card" style="margin-top:var(--space-6);padding:var(--space-4);background-color:var(--surface-bg-alt);">
                <p class="form-group__hint" style="margin:0;">Online payments aren't configured yet. Please contact us to complete this booking.</p>
            </div>
            <?php endif; ?>
            <div class="booking-summary__cta">
                <?php if ($paymentMode === 'test'): ?>
                <p class="form-group__hint" style="text-align:center;">Test Mode &mdash; no real charge will be made.</p>
                <?php endif; ?>
                <button type="button" class="btn btn-accent btn-block btn-lg" id="pay-btn" <?= $paymentConfigured ? '' : 'disabled' ?>>Proceed to Payment</button>
            </div>
        <?php else: ?>
            <div class="booking-summary__cta">
                <a href="/booking/<?= View::e($booking['booking_ref']) ?>/success" class="btn btn-primary btn-block">View confirmation</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if ($paymentProvider === 'razorpay'): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<?php endif; ?>
<script type="module">
import { apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

const payBtn = document.getElementById('pay-btn');
const bookingRef = <?= json_encode($booking['booking_ref']) ?>;

// Stripe Checkout redirects the browser straight back to this page with
// ?stripe_session_id=... in the URL — verify automatically on load instead
// of waiting for a button click (there's no in-page JS callback for a
// hosted-redirect flow the way Razorpay's modal has one).
const stripeSessionId = new URLSearchParams(window.location.search).get('stripe_session_id');
if (stripeSessionId) {
    apiPost('/api/payments/verify', { booking_ref: bookingRef, stripe_session_id: stripeSessionId })
        .then((verifyResult) => { window.location.href = verifyResult.data.redirect; })
        .catch(() => { window.location.href = `/booking/${bookingRef}/failed`; });
}

async function payWithMock(order) {
    try {
        const verifyResult = await apiPost('/api/payments/verify', {
            booking_ref: bookingRef,
            mock_order_id: order.order_id,
        });
        window.location.href = verifyResult.data.redirect;
    } catch {
        window.location.href = `/booking/${bookingRef}/failed`;
    }
}

async function payWithRazorpay(order) {
    const rzp = new Razorpay({
        key: order.key_id,
        amount: order.amount,
        currency: order.currency,
        name: order.name,
        description: order.description,
        order_id: order.order_id,
        theme: { color: '#6c8f6f' },
        handler: async (response) => {
            try {
                const verifyResult = await apiPost('/api/payments/verify', {
                    booking_ref: bookingRef,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_signature: response.razorpay_signature,
                });
                window.location.href = verifyResult.data.redirect;
            } catch {
                window.location.href = `/booking/${bookingRef}/failed`;
            }
        },
        modal: {
            ondismiss: () => {
                toast.info('Payment cancelled.');
                payBtn.classList.remove('is-loading');
                payBtn.disabled = false;
            },
        },
    });

    rzp.on('payment.failed', () => {
        window.location.href = `/booking/${bookingRef}/failed`;
    });

    rzp.open();
}

if (payBtn) {
    payBtn.addEventListener('click', async () => {
        payBtn.classList.add('is-loading');
        payBtn.disabled = true;

        try {
            const result = await apiPost('/api/payments/create-order', { booking_ref: bookingRef });
            const order = result.data;

            if (order.provider === 'stripe') {
                // Stripe Checkout is a hosted payment page — leave the app entirely;
                // the success_url brings the browser back here to auto-verify (above).
                window.location.href = order.redirect_url;
            } else if (order.provider === 'mock') {
                await payWithMock(order);
            } else {
                await payWithRazorpay(order);
            }
        } catch (err) {
            toast.error(err.message);
            payBtn.classList.remove('is-loading');
            payBtn.disabled = false;
        }
    });
}
</script>
