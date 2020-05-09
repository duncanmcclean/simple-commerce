<?php

namespace DoubleThreeDigital\SimpleCommerce\Seeders;

use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Illuminate\Database\Seeder;
use Statamic\Stache\Stache;

class OrderStatusSeeder extends Seeder
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
            OrderStatus::create([
                'uuid'          => (new Stache())->generateId(),
                'name'          => $status['name'],
                'slug'          => $status['slug'],
                'description'   => $status['description'],
                'color'         => $status['color'],
                'primary'       => $status['primary'],
            ]);
        }
    }
}
