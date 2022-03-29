<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\UpdateScripts;

use DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\DummyGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\RunsUpdateScripts;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use DoubleThreeDigital\SimpleCommerce\UpdateScripts\v2_4\MigrateGatewayDataToNewFormat;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Entry;

class MigrateGatewayDataToNewFormatTest extends TestCase
{
    use RunsUpdateScripts, SetupCollections;

    public function setUp(): void
    {
        parent::setUp();

        collect(File::allFiles(base_path('content/collections')))
            ->each(function ($file) {
                File::delete($file);
            });
    }

    /** @test */
    public function it_migrates_full_gateway_data_to_new_format()
    {
        $order = Entry::make()
            ->collection('orders')
            ->data([
                'gateway' => DummyGateway::class,
                'gateway_data' => [
                    'foo' => 'bar',
                    'bar' => 'foo',
                ],
            ]);

        $order->save();

        $this->runUpdateScript(MigrateGatewayDataToNewFormat::class);

        $order->fresh();

        $this->assertIsArray($order->get('gateway'));
        $this->assertNull($order->get('gateway_data'));

        $this->assertSame($order->get('gateway'), [
            'use' => DummyGateway::class,
            'data' => [
                'foo' => 'bar',
                'bar' => 'foo',
            ],
        ]);
    }

    /** @test */
    public function it_migrates_gateway_class_to_new_format()
    {
        $order = Entry::make()
            ->collection('orders')
            ->data([
                'gateway' => DummyGateway::class,
            ]);

        $order->save();

        $this->runUpdateScript(MigrateGatewayDataToNewFormat::class);

        $order->fresh();

        $this->assertIsArray($order->get('gateway'));
        $this->assertNull($order->get('gateway_data'));

        $this->assertSame($order->get('gateway'), [
            'use' => DummyGateway::class,
            'data' => [],
        ]);
    }
}
