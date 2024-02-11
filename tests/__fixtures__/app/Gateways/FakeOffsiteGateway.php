<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\Gateways;

use DuncanMcClean\SimpleCommerce\Contracts\Order as ContractsOrder;
use DuncanMcClean\SimpleCommerce\Gateways\BaseGateway;
use Illuminate\Http\Request;

class FakeOffsiteGateway extends BaseGateway
{
    public function name(): string
    {
        return 'Fake Offsite Gateway';
    }

    public function isOffsiteGateway(): bool
    {
        return true;
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
