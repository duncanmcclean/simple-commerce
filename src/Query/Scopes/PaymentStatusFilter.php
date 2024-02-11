<?php

namespace DuncanMcClean\SimpleCommerce\Query\Scopes;

use DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Support\Runway;
use Statamic\Query\Scopes\Filter;

class PaymentStatusFilter extends Filter
{
    public $pinned = true;

    public static $title = 'Payment Status';

    public function fieldItems()
    {
        return [
            'type' => [
                'type' => 'radio',
                'options' => collect(PaymentStatus::cases())->mapWithKeys(fn ($case) => [
                    $case->value => $case->name,
                ])->toArray(),
            ],
        ];
    }

    public function apply($query, $values)
    {
        return $query->where('payment_status', $values['type']);
    }

    public function badge($values)
    {
        $paymentStatusLabel = PaymentStatus::from($values['type'])->name;

        return "Payment Status: {$paymentStatusLabel}";
    }

    public function visibleTo($key)
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return $key === 'entries'
                && $this->context['collection'] === SimpleCommerce::orderDriver()['collection'];
        }

        if (isset(SimpleCommerce::orderDriver()['model'])) {
            $runwayResource = Runway::orderModel();

            return $key === "runway_{$runwayResource->handle()}";
        }

        return false;
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
