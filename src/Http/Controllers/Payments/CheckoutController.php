<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\Payments;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Events\CouponRedeemed;
use DuncanMcClean\SimpleCommerce\Events\ProductNoStockRemaining;
use DuncanMcClean\SimpleCommerce\Events\ProductStockLow;
use DuncanMcClean\SimpleCommerce\Exceptions\PreventCheckout;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\PaymentGateway;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns\ValidatesStock;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Statamic\Exceptions\NotFoundHttpException;

class CheckoutController
{
    use ValidatesStock;

    public function __invoke(Request $request, ?string $paymentGateway = null)
    {
        $cart = Cart::current();

        if (! $cart->isFree()) {
            $paymentGateway = PaymentGateway::find($paymentGateway);

            throw_if(! $paymentGateway, NotFoundHttpException::class);
        }

        try {
            $this->ensureCouponIsValid($cart, $request);
            $this->ensureProductsAreAvailable($cart, $request);

            throw_if(! $cart->taxableAddress(), new PreventCheckout(__('Order cannot be created without an address.')));
            throw_if(! $cart->customer(), new PreventCheckout(__('Order cannot be created without customer information.')));

            $order = Order::query()->where('cart', $cart->id())->first();

            if (! $order) {
                $order = tap(Order::makeFromCart($cart))->save();
            }

            if ($order->isFree()) {
                $order->status(OrderStatus::PaymentReceived)->save();
            } else {
                $paymentGateway->process($order, $request);
                $order->set('payment_gateway', $paymentGateway::handle())->save();
            }

            $this->updateStock($order);

            if ($order->coupon()) {
                event(new CouponRedeemed($order->coupon(), $order));
            }
        } catch (ValidationException|PreventCheckout $e) {
            $paymentGateway->cancel($cart);

            if ($order = Order::query()->where('cart', $cart->id())->first()) {
                $order->delete();
            }

            return redirect()
                ->route(config('statamic.simple-commerce.routes.checkout'))
                ->withErrors($e->errors());
        }

        Cart::forgetCurrentCart($cart);

        return redirect()->temporarySignedRoute(
            route: config('statamic.simple-commerce.routes.checkout_confirmation'),
            expiration: now()->addHour(),
            parameters: ['order_id' => $order->id()]
        );
    }

    private function ensureCouponIsValid(CartContract $cart, Request $request): void
    {
        if (! $cart->coupon()) {
            return;
        }

        $isValid = $cart->lineItems()->every(fn (LineItem $lineItem) => $cart->coupon()->isValid($cart, $lineItem));

        if (! $isValid) {
            throw new PreventCheckout(__('The coupon code is no longer valid for the items in your cart. Please remove it to continue.'));
        }
    }

    private function ensureProductsAreAvailable(CartContract $cart, Request $request): void
    {
        $cart->lineItems()->each(function (LineItem $lineItem) use ($request, $cart) {
            try {
                $this->validateStock($request, $cart, $lineItem);
            } catch (ValidationException) {
                throw new PreventCheckout(__('One or more items in your cart are no longer available.'));
            }
        });
    }

    private function updateStock(OrderContract $order): void
    {
        $order->lineItems()->each(function (LineItem $lineItem) {
            if ($lineItem->product()->isStandardProduct() && $lineItem->product()->isStockEnabled()) {
                $product = $lineItem->product();

                // When the Price field isn't localized, we need to update the stock on the origin entry.
                if ($product->hasOrigin() && ! $product->blueprint()->field('stock')?->isLocalizable()) {
                    $product = Product::find($product->origin()->id());
                }

                $product->set('stock', $product->stock() - $lineItem->quantity())->save();

                if ($product->stock() < config('statamic.simple-commerce.products.low_stock_threshold')) {
                    event(new ProductStockLow($product));
                }

                if ($product->stock() === 0) {
                    event(new ProductNoStockRemaining($product));
                }
            }

            if ($lineItem->product()->isVariantProduct() && $lineItem->variant()->isStockEnabled()) {
                $product = $lineItem->product();

                // When the Product Variants field isn't localized, we need to update the stock on the origin entry.
                if ($product->hasOrigin() && ! $product->blueprint()->field('product_variants')?->isLocalizable()) {
                    $product = Product::find($product->origin()->id());
                }

                $productVariants = $product->productVariants();

                $productVariants['options'] = collect(Arr::get($productVariants, 'options'))->map(function ($variant) use ($lineItem) {
                    if (isset($variant['stock']) && $variant['key'] === $lineItem->variant()->key()) {
                        $variant['stock'] -= $lineItem->quantity();
                    }

                    return $variant;
                })->all();

                $product->set('product_variants', $productVariants)->save();

                if ($product->stock() < config('statamic.simple-commerce.products.low_stock_threshold')) {
                    event(new ProductStockLow($product->variant($lineItem->variant()->key())));
                }

                if ($product->stock() === 0) {
                    event(new ProductNoStockRemaining($product->variant($lineItem->variant()->key())));
                }
            }
        });
    }
}
