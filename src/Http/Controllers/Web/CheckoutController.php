<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Events\CheckoutComplete;
use Damcclean\Commerce\Events\ProductOutOfStock;
use Damcclean\Commerce\Events\ProductStockRunningLow;
use Damcclean\Commerce\Facades\Product;
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
            ->template('commerce::web.checkout')
            ->layout('commerce::web.layout');
    }

    public function store(Request $request)
    {
        Stripe::setApiKey(config('commerce.stripe.secret'));

        $total = (new CartTags())->total() * 100;

        // WIP allow user to use coupons
            // WIP fire the event when a coupon is used

        // process the payment

        // create/find a commerce customer
            // if customer is new, fire that event
            // if customer is returning, fire that event

        collect($request->session()->get('cart'))
            ->each(function ($cartProduct) {
                $product = Product::findBySlug($cartProduct['slug']);
                $product['stock_number'] -= $cartProduct['quantity'];

                Product::update($product['id'], $product);

                if ($product['stock_number'] == 0) {
                    event(new ProductOutOfStock($product));
                }

                if ($product['stock_number'] <= 5) {
                    event(new ProductStockRunningLow($product));
                }
            });

        // WIP Send notification to store admin
        //event(new CheckoutComplete($order, $customer));

        $request->session()->forget('cart');

        return redirect('/thanks');
    }
}
