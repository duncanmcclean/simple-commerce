<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class OrderStatusControllerTest extends TestCase
{
    /** @test */
    public function can_index_order_statuses()
    {
        $statuses = factory(OrderStatus::class, 5)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('order-status.index'))
            ->assertOk();
    }

    /** @test */
    public function can_store_order_status()
    {
        $this
            ->actAsSuper()
            ->post(cp_route('order-status.store'), [
                'name'          => 'Out for Delivery',
                'slug'          => 'out-for-delivery',
                'description'   => 'This status is used when orders are out for delivery.',
                'color'         => 'red',
            ])
            ->assertCreated();

        $this
            ->assertDatabasehas('order_statuses', [
                'name'  => 'Out for Delivery',
                'color' => 'red',
            ]);
    }

    /** @test */
    public function can_update_order_status()
    {
        $status = factory(OrderStatus::class)->create([
            'color' => 'yellow',
        ]);

        $this
            ->actAsSuper()
            ->post(cp_route('order-status.update', ['status' => $status->uuid]), [
                'name'          => $status->name,
                'slug'          => $status->slug,
                'description'   => $status->description,
                'color'         => 'red',
                'primary'       => $status->primary,
            ])
            ->assertOk();

        $this
            ->assertDatabaseHas('order_statuses', [
                'uuid'  => $status->uuid,
                'color' => 'red',
            ])
            ->assertDatabaseMissing('order_statuses', [
                'uuid'  => $status->uuid,
                'color' => 'yellow',
            ]);
    }

    /** @test */
    public function can_update_order_status_to_be_the_primary()
    {
        $status = factory(OrderStatus::class)->create([
            'color'     => 'yellow',
            'primary'   => false,
        ]);

        $this
            ->actAsSuper()
            ->post(cp_route('order-status.update', ['status' => $status->uuid]), [
                'name'          => $status->name,
                'slug'          => $status->slug,
                'description'   => $status->description,
                'color'         => $status->color,
                'primary'       => true,
            ])
            ->assertOk();

        $this
            ->assertDatabaseHas('order_statuses', [
                'uuid' => $status->uuid,
                'primary' => true,
            ])
            ->assertDatabaseMissing('order_statuses', [
                'uuid' => $status->uuid,
                'primary' => false,
            ]);;
    }

    /** @test */
    public function can_destroy_order_status()
    {
        $status = factory(OrderStatus::class)->create();
        $assignedStatus = factory(OrderStatus::class)->create();

        $this
            ->actAsSuper()
            ->delete(cp_route('order-status.destroy', ['status' => $status->uuid]), [
                'assign' => $assignedStatus->id,
            ])
            ->assertOk();

        $this
            ->assertDatabaseMissing('order_statuses', [
                'uuid' => $status->uuid,
            ]);
    }

    /** @test */
    public function cant_delete_the_only_order_status()
    {
        $status = factory(OrderStatus::class)->create();

        $this
            ->actAsSuper()
            ->delete(cp_route('order-status.destroy', ['status' => $status->uuid]), [
                'assign' => $status->id,
            ])
            ->assertRedirect(cp_route('settings.order-statuses.index'))
            ->assertSessionHas('error');
    }

    /** @test */
    public function cant_delete_the_primary_order_status()
    {
        $status = factory(OrderStatus::class)->create([
            'primary' => true,
        ]);
        $assignedStatus = factory(OrderStatus::class)->create([
            'primary' => false,
        ]);

        $this
            ->actAsSuper()
            ->delete(cp_route('order-status.destroy', ['status' => $status->uuid]), [
                'assign' => $assignedStatus->id,
            ])
            ->assertRedirect(cp_route('settings.order-statuses.index'))
            ->assertSessionHas('error');
    }
}
