<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\Gateways;

use DuncanMcClean\SimpleCommerce\Contracts\Order as ContractsOrder;
use DuncanMcClean\SimpleCommerce\Gateways\BaseGateway;
use Illuminate\Http\Request;

class FakeOnsiteGateway extends BaseGateway
{
    public function name(): string
    {
        return 'Fake Onsite Gateway';
    }

    public function isOffsiteGateway(): bool
    {
        return false;
    }

    public function prepare(Request $request, ContractsOrder $order): array
    {
        return [];
    }

    public function refund(ContractsOrder $order): ?array
    {
        return [];
    }
}
