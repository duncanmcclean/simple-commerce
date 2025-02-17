<?php

namespace DuncanMcClean\SimpleCommerce\Actions;

use DuncanMcClean\SimpleCommerce\Contracts;
use DuncanMcClean\SimpleCommerce\Events\OrderRefunded;
use DuncanMcClean\SimpleCommerce\Facades\PaymentGateway;
use Statamic\Actions\Action;

class Refund extends Action
{
    protected $dangerous = true;

    public static function title()
    {
        return __('Refund');
    }

    public function visibleTo($item)
    {
        return $item instanceof Contracts\Orders\Order
            && $item->get('amount_refunded') < $item->grandTotal();
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function authorize($user, $item)
    {
        return $user->can('refund', $item) && $item->has('payment_gateway');
    }

    protected function fieldItems()
    {
        $order = $this->items->first();

        return [
            'amount' => [
                'display' => __('Amount'),
                'instructions' => __('Enter the amount you wish to refund the customer.'),
                'type' => 'money',
                'default' => $order->grandTotal() - $order->get('amount_refunded'),
                'validate' => 'required|numeric',
            ],
        ];
    }

    /**
     * We're overriding this method in order to set the "parent" of the fields,
     * which is used to determine the currency of the Amount field.
     *
     * @return \Statamic\Fields\Fields
     */
    public function fields()
    {
        $fields = parent::fields();

        if ($order = $this->items->first()) {
            $fields->setParent($order);
        }

        return $fields;
    }

    public function buttonText()
    {
        /** @translation */
        return 'Refund';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to refund this order?';
    }

    public function bypassesDirtyWarning(): bool
    {
        return true;
    }

    public function run($items, $values)
    {
        $order = $this->items->first();
        $amountRemaining = $order->grandTotal() - $order->get('amount_refunded');

        if ($values['amount'] <= 0) {
            throw new \Exception('You must enter an amount greater than 0.');
        }

        if ($amountRemaining < $values['amount']) {
            throw new \Exception('You cannot refund more than the remaining amount.');
        }

        $paymentGateway = PaymentGateway::find($items->first()->get('payment_gateway'));

        $paymentGateway->refund($items->first(), $values['amount']);

        event(new OrderRefunded($order->fresh(), $values['amount']));
    }
}
