<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Tags\CartTags;
use Illuminate\Http\Request;
use Statamic\View\View;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function show()
    {
        return (new View)
            ->template('commerce.checkout')
            ->layout('layout')
            ->with([]);
    }

    public function store(Request $request)
    {
        Stripe::setApiKey(config('commerce.stripe.secret'));

        $intent = PaymentIntent::create([
            'amount' => (new CartTags())->total()*100,
            'currency' => config('commerce.currency'),
            'payment_method_types' => ['card'],
            'metadata' => []
        ]);

        return 'Yahoo! Payment done.';

        // process the payment information to create stripe order & customer
        // create order and customer in addon
        // Send a notification to the customer and the store owner
        // clear the users cart
        // Present user with success message
    }
}
