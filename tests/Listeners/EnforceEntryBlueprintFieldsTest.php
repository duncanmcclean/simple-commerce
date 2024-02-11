<?php

use DuncanMcClean\SimpleCommerce\Listeners\EnforceEntryBlueprintFields;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Facades\Blueprint;

test('fields can be added to customer blueprint', function () {
    $blueprint = Blueprint::make('customer')
        ->setNamespace('collections.customers')
        ->save();

    $event = new EntryBlueprintFound($blueprint);

    $handle = (new EnforceEntryBlueprintFields())->handle($event);

    $this->assertTrue($handle->hasField('orders'));
});

test('fields can be added to product blueprint', function () {
    $blueprint = Blueprint::make('product')
        ->setNamespace('collections.products')
        ->save();

    $event = new EntryBlueprintFound($blueprint);

    $handle = (new EnforceEntryBlueprintFields())->handle($event);

    $this->assertTrue($handle->hasField('product_type'));
    $this->assertTrue($handle->hasField('download_limit'));
    $this->assertTrue($handle->hasField('downloadable_asset'));
});

test('fields can be added to product blueprint with product variants', function () {
    $blueprint = Blueprint::make('product')
        ->setNamespace('collections.products')
        ->setContents([
            'tabs' => ['main' => ['sections' => [
                ['fields' => [
                    [
                        'handle' => 'product_variants',
                        'field' => ['type' => 'product_variants'],
                    ],
                ]],
            ]]],
        ])
        ->save();

    $event = new EntryBlueprintFound($blueprint);

    $handle = (new EnforceEntryBlueprintFields())->handle($event);

    $this->assertTrue($handle->hasField('product_type'));
    $this->assertFalse($handle->hasField('download_limit'));
    $this->assertFalse($handle->hasField('downloadable_asset'));
    $this->assertFalse($handle->hasTab('Digital Product'));
    $this->assertTrue($handle->hasField('product_variants'));

    $optionFields = collect($handle->field('product_variants')->config()['option_fields']);

    $this->assertCount(2, $optionFields);
    $this->assertTrue($optionFields->where('handle', 'download_limit')->count() > 0);
    $this->assertTrue($optionFields->where('handle', 'downloadable_asset')->count() > 0);
});

test('digital product fields are not added to another blueprint', function () {
    $blueprint = Blueprint::make('orders')
        ->setNamespace('collections.orders')
        ->save();

    $event = new EntryBlueprintFound($blueprint);

    $handle = (new EnforceEntryBlueprintFields())->handle($event);

    $this->assertFalse($handle->hasField('product_type'));
    $this->assertFalse($handle->hasField('download_limit'));
    $this->assertFalse($handle->hasField('downloadable_asset'));
});

test('fields can be added to order blueprint', function () {
    $blueprint = Blueprint::make('order')
        ->setNamespace('collections.orders')
        ->save();

    $event = new EntryBlueprintFound($blueprint);

    $handle = (new EnforceEntryBlueprintFields())->handle($event);

    $this->assertTrue($handle->hasField('grand_total'));
    $this->assertTrue($handle->hasField('items_total'));
    $this->assertTrue($handle->hasField('shipping_total'));
    $this->assertTrue($handle->hasField('tax_total'));
    $this->assertTrue($handle->hasField('coupon_total'));
    $this->assertTrue($handle->hasField('status_log'));
    $this->assertTrue($handle->hasField('order_date'));
    $this->assertTrue($handle->hasField('order_status'));
    $this->assertTrue($handle->hasField('payment_status'));
});
