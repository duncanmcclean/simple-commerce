<?php

use DuncanMcClean\SimpleCommerce\UpdateScripts\v6_0\UpdateClassReferences;
use Statamic\Facades\Entry;

it('updates reference to gateway class', function () {
    $orderEntry = Entry::make()
        ->collection('orders')
        ->id('test')
        ->data(['gateway' => ['use' => 'DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\StripeGateway', 'data' => ['id' => 'pi_1234']]]);

    $orderEntry->save();

    (new UpdateClassReferences('duncanmcclean/simple-commerce', '6.0.0'))->update();

    $orderEntry->fresh();

    expect($orderEntry->get('gateway'))->toBe([
        'use' => 'stripe',
        'data' => ['id' => 'pi_1234'],
        'refund' => null,
    ]);
});

it('updates reference to shipping method class', function () {
    $orderEntry = Entry::make()
        ->collection('orders')
        ->id('test')
        ->data(['shipping_method' => 'DoubleThreeDigital\SimpleCommerce\Shipping\FreeShipping']);

    $orderEntry->save();

    (new UpdateClassReferences('duncanmcclean/simple-commerce', '6.0.0'))->update();

    $orderEntry->fresh();

    expect($orderEntry->get('shipping_method'))->toBe('free_shipping');
});
