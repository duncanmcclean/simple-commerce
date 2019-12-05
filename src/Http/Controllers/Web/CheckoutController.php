<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

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
            ->template('commerce.checkout')
            ->layout('layout')
            ->with([]);
    }

    public function store(Request $request)
    {
//        Stripe::setApiKey(config('commerce.stripe.secret'));
//
//        $total = (new CartTags())->total()*100;
//
//        // WIP change the total amount based on coupons
//
//        $intent = PaymentIntent::create([
//            'amount' => $total,
//            'currency' => config('commerce.currency'),
//            'payment_method_types' => ['card'],
//            'metadata' => []
//        ]);
//
//        // WIP use real stripe customer id as filename here
//        $commerceCustomer = Customer::save('cus_'.uniqid(), [
//            'name' => $request->name,
//            'email' => $request->email,
//            'address' => $request->address,
//            'country' => $request->country,
//            'zip_code' => $request->zip_code,
//            'currency' => config('commerce.currency'),
//            'stripe_customer_id' => '',
//        ]);
//
//        // WIP use real stripe order ID (or something better than this)
//        $commerceOrder = Order::save('ord_'.uniqid(), [
//            'status' => 'created',
//            'total' => $total,
//            'shipping_address' => $request->address,
//            'coupon' => '',
//            'stripe_customer_id' => '' // WIP use real stripe customer id here too
//        ]);

        // WIP Send notification to customer
        // WIP Send notification to store admin
        // WIP take quantity of order off the stock number for all products purchased

        collect($request->session()->get('cart'))
            ->each(function ($cartProduct) {
                $product = Product::getBySlug($cartProduct['slug']);
                $product->stock_number -= $cartProduct['quantity'];
                $product->save();

                Product::update($product->slug, (array) $product);
            });

        $request->session()->forget('cart');

        return redirect('/thanks');
    }
}
