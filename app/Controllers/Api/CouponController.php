<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Coupon;

final class CouponController extends Controller
{
    public function validateCode(Request $request): void
    {
        $code = strtoupper(trim((string) $request->input('code', '')));
        $subtotal = (float) $request->input('subtotal', 0);

        $coupon = Coupon::findValidByCode($code);

        if (!$coupon) {
            $this->fail('This coupon code is invalid or has expired.', 404);
        }

        if ($subtotal < (float) $coupon['min_order_amount']) {
            $this->fail(sprintf('This coupon requires a minimum order of %.2f.', $coupon['min_order_amount']), 422);
        }

        $discount = $coupon['discount_type'] === 'percent'
            ? round($subtotal * ((float) $coupon['discount_value'] / 100), 2)
            : (float) $coupon['discount_value'];

        $discount = min($discount, $subtotal);

        $this->success([
            'coupon_id' => $coupon['id'],
            'code' => $coupon['code'],
            'discount_type' => $coupon['discount_type'],
            'discount_value' => (float) $coupon['discount_value'],
            'discount_amount' => $discount,
        ]);
    }
}
