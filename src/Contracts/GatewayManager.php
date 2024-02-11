<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use Illuminate\Http\Request;

interface GatewayManager
{
    public function use($className): self;

    public function config();

    public function name();

    public function isOffsiteGateway(): bool;

    public function prepare(Request $request, Order $order);

    public function checkout(Request $request, Order $order);

    public function checkoutRules();

    public function checkoutMessages();

    public function refund(Order $order): array;

    public function callback(Request $request): bool;

    public function webhook(Request $request);

    public function fieldtypeDisplay($value): array;

    public function callbackUrl(array $extraParamters = []): string;

    public function withRedirectUrl(string $redirectUrl): self;

    public function withErrorRedirectUrl(string $errorRedirectUrl): self;

    public function resolve();
}
