<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Events\CheckoutComplete;
use Damcclean\Commerce\Events\ProductOutOfStock;
use Damcclean\Commerce\Events\ProductStockRunningLow;
use Damcclean\Commerce\Facades\Product;
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
//        Stripe::setApiKey(config('commerce.stripe.secret'));
//
//        $total = (new CartTags())->total()*100;
//
//        // WIP change the total amount based on coupons
        // fire the correct event when a coupon is used
//
//        $intent = PaymentIntent::create([
//            'amount' => $total,
//            'currency' => config('commerce.currency'),
//            'payment_method_types' => ['card'],
//            'metadata' => []
//        ]);
//
//        // WIP use real stripe customer id as filename here
//        $customer = Customer::save('cus_'.uniqid(), [
//            'name' => $request->name,
//            'email' => $request->email,
//            'address' => $request->address,
//            'country' => $request->country,
//            'zip_code' => $request->zip_code,
//            'currency' => config('commerce.currency'),
//            'stripe_customer_id' => '',
//        ]);

        // if customer is new, fire that event
        // if customer is returning, fire that event

//
//        // WIP use real stripe order ID (or something better than this)
//        $order = Order::save('ord_'.uniqid(), [
//            'status' => 'created',
//            'total' => $total,
//            'shipping_address' => $request->address,
//            'coupon' => '',
//            'stripe_customer_id' => '' // WIP use real stripe customer id here too
//        ]);

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

        // WIP Send notification to customer
        // WIP Send notification to store admin
        //event(new CheckoutComplete($order, $customer));

        $request->session()->forget('cart');

        return redirect('/thanks');
    }
}
