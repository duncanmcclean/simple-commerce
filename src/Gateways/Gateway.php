<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Models\Transaction;
use Illuminate\Support\Collection;

interface Gateway
{
    /**
     * @param array $data
     * @param float $total
     *
     * @return Collection
     */
    public function completePurchase(array $data, float $total): Collection;

    /**
     * @return array
     */
    public function rules(): array;

    /**
     * @return string
     */
    public function paymentForm(): string;

    /**
     * @param Transaction $transaction
     *
     * @return Collection
     */
    public function refund(Transaction $transaction): Collection;

    /**
     * @return string
     */
    public function name(): string;
}
