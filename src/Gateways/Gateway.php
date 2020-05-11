<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Models\Transaction;
use Illuminate\Support\Collection;

interface Gateway
{
    public function completePurchase(array $data, float $total): Collection;

    public function rules(): array;

    public function paymentForm(): string;

    public function refund(Transaction $transaction): Collection;

    public function name(): string;
}
