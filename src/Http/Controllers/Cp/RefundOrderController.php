<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Models\Order;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Stripe\Refund;
use Stripe\Stripe;

class RefundOrderController extends CpController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);

        Stripe::setApiKey(config('commerce.stripe.secret'));
    }

    public function store(Order $order)
    {
        $this->authorize('refund', $order);

        if (! $order->payment_intent) {
            return back()->with('error', 'Refund failed because there is no PaymentIntent.');
        }

        Refund::create([
            'payment_intent' => $order->payment_intent,
        ]);

        // TODO: do something to the order so the user knows that the order has been refunded.

        return back()->with('success', 'Order has been refunded.');
    }
}
