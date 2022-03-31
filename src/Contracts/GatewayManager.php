<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use Illuminate\Http\Request;

interface GatewayManager
{
    public function use($className): self;

    public function name();

    public function config();

    public function prepare($request, $order);

    public function purchase($request, $order);

    public function purchaseRules();

    public function purchaseMessages();

    public function getCharge($order);

    public function refundCharge($order);

    public function callback(Request $request);

    public function callbackUrl(array $extraParamters = []);

    public function webhook(Request $request);

    public function isOffsiteGateway(): bool;

    public function withRedirectUrl(string $redirectUrl): self;

    public function withErrorRedirectUrl(string $errorRedirectUrl): self;
}
