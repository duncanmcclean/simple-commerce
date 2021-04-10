<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Console\Processes\Composer;
use Statamic\UpdateScripts\UpdateScript;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class UpdateConfig extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.3.0');
    }

    public function update()
    {
        $this
            ->handleGatewayConfig()
            ->handleNotificationConfig()
            ->handleContentConfig();
    }

    protected function handleGatewayConfig(): self
    {
        $contents = Str::of(File::get(config_path('simple-commerce.php')))
            ->replace("DoubleThreeDigital\\SimpleCommerce\\Gateways\\DummyGateway", "DoubleThreeDigital\\SimpleCommerce\\Gateways\\Builtin\\DummyGateway")
            ->replace("DoubleThreeDigital\\SimpleCommerce\\Gateways\\MollieGateway", "DoubleThreeDigital\\SimpleCommerce\\Gateways\\Builtin\\MollieGateway")
            ->replace("DoubleThreeDigital\\SimpleCommerce\\Gateways\\StripeGateway", "DoubleThreeDigital\\SimpleCommerce\\Gateways\\Builtin\\StripeGateway")
            ->__toString();

        File::put(config_path('simple-commerce.php'), $contents);

        $this->console()->info("Updated gateways config");

        return $this;
    }

    protected function handleNotificationConfig(): self
    {
        ConfigWriter::edit('simple-commerce')
            ->replace('notifications', [
                'order_paid' => [
                    \DoubleThreeDigital\SimpleCommerce\Notifications\CustomerOrderPaid::class   => ['to' => 'customer'],
                    \DoubleThreeDigital\SimpleCommerce\Notifications\BackOfficeOrderPaid::class => ['to' => 'duncan@example.com'],
                ],
            ])
            ->save();

        $this->console()->info("Updated notifications config");

        return $this;
    }

    protected function handleContentConfig(): self
    {
        $helpComment = <<<'BLOCK'
        /*
        |--------------------------------------------------------------------------
        | Content Drivers
        |--------------------------------------------------------------------------
        |
        | Simple Commerce stores all products, orders, coupons etc as flat-file entries.
        | This works great for store stores where you want to keep everything simple. But
        | sometimes, for more complex stores, you may want use a database instead. To do so,
        | just swap out the 'content driver' in place below.
        |
        */
        BLOCK;

        ConfigWriter::edit('simple-commerce')
            ->replaceStructure('collections', 'content', [
                'orders' => [
                    'driver' => \DoubleThreeDigital\SimpleCommerce\Orders\Order::class,
                    'collection' => config('simple-commerce.collections.orders'),
                ],

                'products' => [
                    'driver' => \DoubleThreeDigital\SimpleCommerce\Products\Product::class,
                    'collection' => config('simple-commerce.collections.products'),
                ],

                'coupons' => [
                    'driver' => \DoubleThreeDigital\SimpleCommerce\Coupons\Coupon::class,
                    'collection' => config('simple-commerce.collections.coupons'),
                ],

                'customers' => [
                    'driver' => \DoubleThreeDigital\SimpleCommerce\Customers\Customer::class,
                    'collection' => config('simple-commerce.collections.customers'),
                ],
            ], $helpComment, true)
            ->save();

        // TODO: https://github.com/Stillat/proteus/issues/9
        // ConfigWriter::edit('simple-commerce')
        //     ->remove('taxonomies')
        //     ->save();

        return $this;
    }
}
