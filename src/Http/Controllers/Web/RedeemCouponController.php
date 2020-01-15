<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Helpers\Cart;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\RedeemCouponRequest;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class RedeemCouponController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('commerce.stripe.secret'));

        $this->cart = new Cart();
    }

    public function __invoke(RedeemCouponRequest $request)
    {
        $validate = $request->validated();

        $coupon = Coupon::findByCode($request->code);

        if ($coupon === null) {
            return $this->throwError('The coupon you provided is invalid.');
        }

        // TODO: Check that the current date is between the two valid dates

        if ($coupon['enabled'] != true) {
            return $this->throwError('The coupon you provided has been disabled.');
        }

        // TODO: do some crazy calculation stuff

        // TODO: add the coupon to the customer's session

        // TODO: generate new cart total

        return $this->throwNonError('The coupon you provided has been applied to your cart.', [
            'intent' => PaymentIntent::create([
                'amount' => (number_format($this->cart->total(), 2, '.', '') * 100),
                'currency' => config('commerce.currency.code'),
            ])->client_secret,
        ]);
    }

    public function throwError(string $message, array $data = [])
    {
        return response()->json(array_merge($data, [
            'error' => true,
            'message' => $message,
        ]));
    }

    public function throwNonError(string $message, array $data = [])
    {
        return response()->json(array_merge($data, [
            'error' => false,
            'message' => $message,
        ]));
    }
}
