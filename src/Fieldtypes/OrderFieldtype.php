<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use Statamic\Fieldtypes\Relationship;
use Statamic\Statamic;

class OrderFieldtype extends Relationship
{
    protected $canEdit = true;
    protected $canCreate = false;
    protected $canSearch = false;

    protected $formComponent = 'order-publish-form';

    protected $formComponentProps = [
        'initialActions' => 'actions',
        'initialTitle' => 'title',
        'initialReference' => 'reference',
        'initialFieldset' => 'blueprint',
        'initialValues' => 'values',
        'initialMeta' => 'meta',
        'initialReadOnly' => 'readOnly',
        'breadcrumbs' => 'breadcrumbs',
    ];

    protected function toItemArray($id)
    {
        $order = Order::find($id);

        return [
            'id' => $order->id(),
            'reference' => $order->reference(),
            'title' => "#{$order->orderNumber()}",
            'hint' => $order->date()->format(Statamic::cpDateFormat()),
            'edit_url' => cp_route('simple-commerce.orders.edit', $order->id()),
        ];
    }

    public function getIndexItems($request)
    {
        // TODO: Implement getIndexItems() method.
    }

    public function augment($values)
    {
        return collect($values)->map(fn ($id) => Order::find($id)?->toShallowAugmentedArray())->filter()->all();
    }
}