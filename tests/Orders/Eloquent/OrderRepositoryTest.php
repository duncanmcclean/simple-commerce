<?php

namespace Tests\Feature\SimpleCommerce;

use DoubleThreeDigital\SimpleCommerce\Orders\Eloquent\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\Eloquent\OrderRepository;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_can_get_all_orders()
    {
        $orders = $this->orderFactory([], 5);

        $repository = (new OrderRepository);
        $all = $repository->all();

        $this->assertTrue($all instanceof \Illuminate\Support\Collection);
        $this->assertSame($all->count(), 5);
    }

    /** @test */
    public function it_can_query_orders()
    {
        $orders = $this->orderFactory([], 5);
        $orders[4]->update(['billing_country' => 'UK']);

        $repository = (new OrderRepository);
        $query = $repository->query();

        $this->assertTrue($query instanceof \Illuminate\Database\Eloquent\Builder);

        $ordersBilledInUk = $query->where('billing_country', 'UK')->get();

        $this->assertTrue($ordersBilledInUk instanceof \Illuminate\Support\Collection);
        $this->assertSame($ordersBilledInUk->count(), 1);
    }

    /** @test */
    public function it_can_find_order()
    {
        $order = $this->orderFactory();

        $repository = (new OrderRepository);
        $find = $repository->find((string) $order->id);

        $this->assertTrue($find instanceof OrderRepository);
    }

    /** @test */
    public function it_can_create_order()
    {
        $repository = (new OrderRepository);

        $create = $repository->create([
            'title'   => '#1000',
            'is_paid' => true,
        ]);

        $this->assertTrue($create instanceof OrderRepository);

        $this->assertDatabaseHas('orders', [
            'title'   => '#1000',
            'is_paid' => true,
        ]);
    }

    /** @test */
    public function it_can_save_order()
    {
        $order = $this->orderFactory();

        $repository = (new OrderRepository);

        $save = $repository->find((string) $order->id)->data([
            'billing_name' => 'John Doe',
        ])->save();

        $this->assertTrue($save instanceof OrderRepository);

        $this->assertDatabaseMissing('orders', [
            'billing_name' => $order->title,
        ]);

        $this->assertDatabaseHas('orders', [
            'billing_name' => 'John Doe',
        ]);
    }

    /** @test */
    public function it_can_delete_order()
    {
        $order = $this->orderFactory();

        $repository = (new OrderRepository);
        $delete = $repository->find((string) $order->id)->delete();

        $this->assertNull($delete);

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
            'title' => $order->title,
        ]);
    }

    /** @test */
    public function it_can_return_id()
    {
        $order = $this->orderFactory();

        $repository = (new OrderRepository);
        $id = $repository->find((string) $order->id)->id();

        $this->assertSame($id, $order->id);
    }

    /** @test */
    public function it_can_get_attribute()
    {
        $order = $this->orderFactory();

        $repository = (new OrderRepository);
        $get = $repository->find((string) $order->id)->get('billing_name');

        $this->assertSame($get, $order->billing_name);
    }

    /** @test */
    public function it_can_set_attribute()
    {
        $order = $this->orderFactory(['billing_name' => 'Smithy']);

        $repository = (new OrderRepository);
        $set = $repository->find((string) $order->id)->set('billing_name', 'Smiths')->save();

        $this->assertSame($set->data['billing_name'], 'Smiths');
    }

    /** @test */
    public function it_can_return_attribute_has()
    {
        $order = $this->orderFactory();

        $repository = (new OrderRepository);
        $has = $repository->find((string) $order->id)->has('billing_name');

        $this->assertTrue($has);
    }

    /** @test */
    public function it_can_return_order_as_array()
    {
        $order = $this->orderFactory();

        $repository = (new OrderRepository);
        $toArray = $repository->find((string) $order->id)->toArray();

        $this->assertIsArray($toArray);
    }

    protected function orderFactory(array $attributes = [], int $count = 1)
    {
        $models = [];

        $default = [
            'is_paid' => false,
            'grand_total' => 0,
            'items_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'coupon_total' => 0,
            'billing_name' => $this->faker->name(),
            'billing_address' => $this->faker->address,
            'billing_city' => $this->faker->city,
            'billing_postal_code' => $this->faker->postcode,
            'billing_country' => $this->faker->country,
            'shipping_name' => $this->faker->name(),
            'shipping_address' => $this->faker->address,
            'shipping_city' => $this->faker->city,
            'shipping_postal_code' => $this->faker->postcode,
            'shipping_country' => $this->faker->country,
            'gateway' => (string) DummyGateway::class,
            'stripe' => null,
            'gateway_data' => null,
            'coupon_id' => null,
            'user_id' => null,
            'paid_at' => null,
        ];

        for ($i=0; $i < $count; $i++) {
            $models[] = Order::create(array_merge($attributes, $default));
        }

        return count($models) > 1
            ? $models
            : $models[0];
    }
}
