<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\Payments;

use DuncanMcClean\SimpleCommerce\Facades\PaymentGateway;
use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;

class WebhookController
{
    public function __invoke(Request $request, string $paymentGateway)
    {
        $paymentGateway = PaymentGateway::find($paymentGateway);

        throw_if(! $paymentGateway, NotFoundHttpException::class);

        $paymentGateway->webhook($request);

        return 'Webhook handled';
    }
}
