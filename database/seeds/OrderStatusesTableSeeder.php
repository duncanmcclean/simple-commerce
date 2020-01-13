<?php

use Damcclean\Commerce\Models\OrderStatus;
use Illuminate\Database\Seeder;
use Statamic\Stache\Stache;

class OrderStatusesTableSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            [
                'name' => 'New',
                'slug' => 'new',
                'description' => 'Default status for new orders',
                'color' => 'green',
                'primary' => true,
            ],
            [
                'name' => 'Shipped',
                'slug' => 'shipped',
                'description' => 'Status for an order that has been shipped.',
                'color' => 'blue',
                'primary' => false,
            ],
        ];

        foreach ($statuses as $status) {
            $item = new OrderStatus();
            $item->uid = (new Stache())->generateId();
            $item->name = $status['name'];
            $item->slug = $status['slug'];
            $item->description = $status['description'];
            $item->color = $status['color'];
            $item->primary = $status['primary'];
            $item->save();
        }
    }
}
