<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Checkout;

use Closure;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Events\DigitalDownloadReady;
use DuncanMcClean\SimpleCommerce\Facades\LicenseKey;
use Illuminate\Support\Facades\URL;

class HandleDigitalProducts
{
    public function handle(Order $order, Closure $next)
    {
        $hasDownloads = $order->lineItems()
            ->filter(function ($lineItem) {
                return $lineItem->product()->get('product_type') === 'digital';
            })
            ->each(function ($lineItem) use ($order) {
                $order->updateLineItem($lineItem->id(), [
                    'metadata' => array_merge($lineItem->metadata()->toArray(), [
                        'license_key' => $licenseKey = LicenseKey::generate(),
                        'download_url' => URL::signedRoute('statamic.simple-commerce.digital-products.download', [
                            'orderId' => $order->id,
                            'lineItemId' => $lineItem->id(),
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
