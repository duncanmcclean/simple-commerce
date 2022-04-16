<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders;

use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Tests\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\RefreshContent;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

class EntryOrderRepositoryTest extends TestCase
{
    use SetupCollections, RefreshContent;

    protected $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = new EntryOrderRepository;
    }

    /** @test */
    public function can_generate_order_number_from_minimum_order_number()
    {
        $this->app['config']->set('simple-commerce.minimum_order_number', 1000);

        $generateOrderNumber = (new Invader($this->repository))->generateOrderNumber();

        $this->assertSame($generateOrderNumber, 1000);
    }

    /** @test */
    public function can_generate_order_number_from_previous_order_titles()
    {
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

        $this->assertSame($generateOrderNumber, 1237);
    }

    /** @test */
    public function can_generate_order_number_from_previous_order_number()
    {
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

        $this->assertSame($generateOrderNumber, 6004);
    }
}
