<?php

use Damcclean\Commerce\Models\Product;
use Damcclean\Commerce\Models\ProductCategory;
use Damcclean\Commerce\Models\Variant;
use Illuminate\Database\Seeder;
use Statamic\Stache\Stache;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        // Categories
        $categories = [
            [
                'title' => 'Clothing',
                'slug' => 'clothing',
            ],
        ];

        foreach ($categories as $category) {
            $item = new ProductCategory();
            $item->uid = (new Stache())->generateId();
            $item->title = $category['title'];
            $item->slug = $category['slug'];
            $item->save();
        }

        // Products
        $products = [
            [
                'title' => 'T-shirt',
                'slug' => 't-shirt',
                'description' => '',
                'category' => 'clothing',
                'enabled' => true,
            ],
            [
                'title' => 'Waterproof Jacket',
                'slug' => 'waterproof-jacket',
                'description' => '',
                'category' => 'clothing',
                'enabled' => true,
            ],
        ];

        foreach ($products as $product) {
            $item = new Product();
            $item->uid = (new Stache())->generateId();
            $item->title = $product['title'];
            $item->slug = $product['slug'];
            $item->description = $product['description'];
            $item->product_category_id = (ProductCategory::where('slug', $product['category'])->first())->id;
            $item->is_enabled = $product['enabled'];
            $item->save();
        }

        // Variants
        $variants = [
            [
                'sku' => 'TSHRSMA',
                'description' => 'Small t-shirt',
                'attributes' => [
                    'size' => 'Small',
                ],
                'price' => '1500',
                'stock' => '25',
                'unlimited_stock' => false,
                'max_quantity' => 3,
                'product' => 't-shirt',
            ],
            [
                'sku' => 'TSHRMED',
                'description' => 'Medium t-shirt',
                'attributes' => [
                    'size' => 'Medium',
                ],
                'price' => '1500',
                'stock' => '40',
                'unlimited_stock' => false,
                'max_quantity' => 5,
                'product' => 't-shirt',
            ],
            [
                'sku' => 'TSHRLAR',
                'description' => 'Large t-shirt',
                'attributes' => [
                    'size' => 'Large',
                ],
                'price' => '1500',
                'stock' => '15',
                'unlimited_stock' => false,
                'max_quantity' => 2,
                'product' => 't-shirt',
            ],
            [
                'sku' => 'WATJAKORN',
                'description' => 'Orange',
                'attributes' => [
                    'color' => 'Orange',
                ],
                'price' => '400',
                'stock' => '50',
                'unlimited_stock' => false,
                'max_quantity' => 5,
                'product' => 'waterproof-jacket',
            ],
            [
                'sku' => 'WATJAKBLA',
                'description' => 'Black',
                'attributes' => [
                    'color' => 'Black',
                ],
                'price' => '400',
                'stock' => '50',
                'unlimited_stock' => false,
                'max_quantity' => 5,
                'product' => 'waterproof-jacket',
            ],
            [
                'sku' => 'WATJAKGRE',
                'description' => 'Green',
                'attributes' => [
                    'color' => 'Green',
                ],
                'price' => '400',
                'stock' => '50',
                'unlimited_stock' => false,
                'max_quantity' => 5,
                'product' => 'waterproof-jacket',
            ],
        ];

        foreach ($variants as $variant) {
            $item = new Variant();
            $item->uid = (new Stache())->generateId();
            $item->sku = $variant['sku'];
            $item->description = $variant['description'];
            $item->variant_attributes = $variant['attributes'];
            $item->price = $variant['price'];
            $item->stock = $variant['stock'];
            $item->unlimited_stock = $variant['unlimited_stock'];
            $item->max_quantity = $variant['max_quantity'];
            $item->product_id = (Product::where('slug', $variant['product'])->first())->id;
            $item->save();
        }
    }
}
