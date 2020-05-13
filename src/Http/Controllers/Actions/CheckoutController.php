<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Actions;

use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful;
use DoubleThreeDigital\SimpleCommerce\Events\VariantLowStock;
use DoubleThreeDigital\SimpleCommerce\Events\VariantOutOfStock;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CheckoutRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use DoubleThreeDigital\SimpleCommerce\Models\LineItem;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class CheckoutController
{
    public $request;
    public $order;

    public function store(CheckoutRequest $request)
    {
        $this->request = $request;
        $this->order = Order::where('uuid', Session::get(config('simple-commerce.cart_session_key')))->first();

        $this->processPayment();
        $this->processCustomer();
        $this->processStockUpdates();
        $this->processCoupon();

        $this->order->update(['is_completed' => true]);
        Event::dispatch(new OrderSuccessful($this->order));
        Session::remove(config('simple-commerce.cart_session_key'));

        return 
            $request->_redirect ? 
            redirect($request->_redirect)->with('order', $this->order->templatePrep())->with('receipt', $this->order->generateReceipt()) :
            back()->with('order', $this->order->templatePrep())->with('receipt', $this->order->generateReceipt());
    }

    protected function processPayment()
    {
        $gateway = (new $this->request->gateway)
            ->completePurchase($this->request->all(), $this->order->total);

        if ($gateway->get('is_complete') === true) {
            $this->order->update(['is_paid' => true]);
            Event::dispatch(new OrderPaid($this->order));
        }

        $this->order->transactions()->create([
            'gateway'   => $this->request->gateway,
            'amount'    => $gateway->get('amount'),
            'is_complete' => $gateway->get('is_complete'),
            'is_refunded' => false,
            'gateway_data' => $gateway->get('data'),
            'order_id' => $this->order->id,
            'currency_id' => Currency::get()['id'],
        ]);
    }

    protected function processCustomer()
    {
        if (Auth::guest()) {
            $customerModel = config('simple-commerce.customers.model');
            $customerModel = new $customerModel();

            $customer = $customerModel::where('email', $this->request->email)->first();

            if ($customer === null) {
                $customer = new $customerModel();
                $fields = $customerModel->fields;

                collect($this->request->all())
                    ->reject(function ($value, $key) use ($fields) {
                        return ! in_array($key, $fields);
                    })
                    ->each(function ($value, $key) use ($customer) {
                        $customer->{$key} = $value;
                    })
                    ->toArray();

                if (! $customer->password) {
                    $customer->password = Hash::make(uniqid().'ssspppp');
                }

                $customer->save();
            }
        } else {
            $customer = Auth::user();
        }

        $this->order->update(['customer_id' => $customer->id]);
        $this->order->billingAddress()->update(['customer_id' => $customer->id]);
        $this->order->shippingAddress()->update(['customer_id' => $customer->id]);
        $this->order->transactions()->update(['customer_id' => $customer->id]);
    }

    public function processStockUpdates()
    {
        collect($this->order->lineItems)
            ->reject(function (LineItem $lineItem) {
                if ($lineItem->variant->unlimited_stock) {
                    return true;
                }

                return false;
            })
            ->each(function (LineItem $lineItem) {
                $lineItem->variant()->update([
                    'stock' => ($lineItem->variant->stock - $lineItem->quantity),
                ]);

                if ($lineItem->variant->stock <= config('simple-commerce.low_stock_counter')) {
                    Event::dispatch(new VariantLowStock($lineItem->variant));
                }

                if ($lineItem->variant->stock === 0) {
                    Event::dispatch(new VariantOutOfStock($lineItem->variant));
                }
            });
    }

    public function processCoupon()
    {
        $order = $this->order;

        collect($order->lineItems)
            ->reject(function (LineItem $lineItem) {
                if (! $lineItem->coupon_id) {
                    return true;
                }

                return false;
            })
            ->map(function (LineItem $lineItem) {
                return $lineItem->coupon_id;
            })
            ->unique()
            ->each(function ($couponId) use ($order) {
                Event::dispatch(new CouponRedeemed(Coupon::find($couponId), $order));
            });
    }
}
