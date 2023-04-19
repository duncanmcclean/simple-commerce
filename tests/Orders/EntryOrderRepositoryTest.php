<?php

use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\RefreshContent;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

uses(TestCase::class);
uses(SetupCollections::class);
uses(RefreshContent::class);
beforeEach(function () {
    $this->repository = new EntryOrderRepository;
});


test('can generate order number from minimum order number', function () {
    app()['config']->set('simple-commerce.minimum_order_number', 1000);

    $generateOrderNumber = (new Invader($this->repository))->generateOrderNumber();

    expect(1000)->toBe($generateOrderNumber);
});

test('can generate order number from previous order titles', function () {
    Collection::find('orders')->titleFormats([])->save();

    Entry::make()
        ->collection('orders')
        ->data(['title' => '#1234'])
        ->save();

    Entry::make()
        ->collection('orders')
        ->data(['title' => '#1235'])
        ->save();

    Entry::make()
        ->collection('orders')
        ->data(['title' => '#1236'])
        ->save();

    $generateOrderNumber = (new Invader($this->repository))->generateOrderNumber();

    expect(1237)->toBe($generateOrderNumber);
});

test('can generate order number from previous order number', function () {
    Collection::find('orders')->titleFormats([
        'default' => '#{order_number}',
    ])->save();

    Entry::make()
        ->collection('orders')
        ->data(['order_number' => 6001])
        ->save();

    Entry::make()
        ->collection('orders')
        ->data(['order_number' => 6002])
        ->save();

    Entry::make()
        ->collection('orders')
        ->data(['order_number' => 6003])
        ->save();

    $generateOrderNumber = (new Invader($this->repository))->generateOrderNumber();

    expect(6004)->toBe($generateOrderNumber);
});
