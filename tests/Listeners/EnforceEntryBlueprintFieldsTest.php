<?php

use DoubleThreeDigital\SimpleCommerce\Listeners\EnforceEntryBlueprintFields;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;

test('fields can be added to product blueprint', function () {
    $blueprint = Blueprint::make('product')
        ->setNamespace('collections.products')
        ->save();

    $event = new EntryBlueprintFound($blueprint);

    $handle = (new EnforceEntryBlueprintFields())->handle($event);

    $this->assertTrue($handle->hasField('is_digital_product'));
    $this->assertTrue($handle->hasField('download_limit'));
    $this->assertTrue($handle->hasField('downloadable_asset'));
    $this->assertTrue($handle->hasTab('Digital Product'));
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

    $this->assertFalse($handle->hasField('is_digital_product'));
    $this->assertFalse($handle->hasField('download_limit'));
    $this->assertFalse($handle->hasField('downloadable_asset'));
    $this->assertFalse($handle->hasTab('Digital Product'));
    $this->assertTrue($handle->hasField('product_variants'));

    $optionFields = collect($handle->field('product_variants')->config()['option_fields']);

    $this->assertCount(3, $optionFields);
    $this->assertTrue($optionFields->where('handle', 'is_digital_product')->count() > 0);
    $this->assertTrue($optionFields->where('handle', 'download_limit')->count() > 0);
    $this->assertTrue($optionFields->where('handle', 'downloadable_asset')->count() > 0);
});

test('digital product fields are not added to another blueprint', function () {
    $blueprint = Blueprint::make('orders')
        ->setNamespace('collections.orders')
        ->save();

    $event = new EntryBlueprintFound($blueprint);

    $handle = (new EnforceEntryBlueprintFields())->handle($event);

    $this->assertFalse($handle->hasField('is_digital_product'));
    $this->assertFalse($handle->hasField('download_limit'));
    $this->assertFalse($handle->hasField('downloadable_asset'));
    $this->assertFalse($handle->hasTab('Digital Product'));
});
