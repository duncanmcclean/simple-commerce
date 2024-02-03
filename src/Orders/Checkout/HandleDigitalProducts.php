<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Checkout;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Events\DigitalDownloadReady;
use DoubleThreeDigital\SimpleCommerce\Facades\LicenseKey;
use DoubleThreeDigital\SimpleCommerce\Products\ProductType;
use Illuminate\Support\Facades\URL;

class HandleDigitalProducts
{
    public function handle(Order $order, Closure $next)
    {
        $hasDownloads = $order->lineItems()
            ->filter(function ($lineItem) {
                $product = $lineItem->product();

                if ($product->purchasableType() === ProductType::Variant) {
                    $productVariant = $product->variant($lineItem->variant()['variant'] ?? $lineItem->variant());

                    return $productVariant->has('is_digital_product')
                        ? $productVariant->get('is_digital_product')
                        : false;
                }

                return $product->has('is_digital_product')
                    ? $product->get('is_digital_product')
                    : false;
            })
            ->each(function ($lineItem) use ($order) {
                $order->updateLineItem($lineItem->id(), [
                    'metadata' => array_merge($lineItem->metadata()->toArray(), [
                        'license_key' => $licenseKey = LicenseKey::generate(),
                        'download_url' => URL::signedRoute('statamic.digital-downloads.download', [
                            'order_id' => $event->order->id,
                            'item_id' => $lineItem->id(),
                            'license_key' => $licenseKey,
                        ]),
                        'download_history' => [],
                    ]),
                ]);
            });

        if ($hasDownloads->count() >= 1 && $customer = $order->customer()) {
            event(new DigitalDownloadReady($order));
        }

        return $next($order);
    }
}
