<?php

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
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
                'name' => 'Small t-shirt',
                'description' => 'A small version of the generic t-shirt that we sell.',
                'attributes' => [
                    [
                        '_id' => 'row-1',
                        'key' => 'Size',
                        'value' => 'Small',
                    ],
                ],
                'price' => '1500',
                'stock' => '25',
                'unlimited_stock' => false,
                'max_quantity' => 3,
                'product' => 't-shirt',
            ],
            [
                'sku' => 'TSHRMED',
                'name' => 'Medium t-shirt',
                'description' => 'A medium version of the generic t-shirt that we sell.',
                'attributes' => [
                    [
                        '_id' => 'row-1',
                        'key' => 'Size',
                        'value' => 'Medium',
                    ],
                ],
                'price' => '1500',
                'stock' => '40',
                'unlimited_stock' => false,
                'max_quantity' => 5,
                'product' => 't-shirt',
            ],
            [
                'sku' => 'TSHRLAR',
                'name' => 'Large t-shirt',
                'description' => 'A large version of the generic t-shirt that we sell.',
                'attributes' => [
                    [
                        '_id' => 'row-1',
                        'key' => 'Size',
                        'value' => 'Large',
                    ],
                ],
                'price' => '1500',
                'stock' => '15',
                'unlimited_stock' => false,
                'max_quantity' => 2,
                'product' => 't-shirt',
            ],
            [
                'sku' => 'WATJAKORN',
                'title' => 'Orange',
                'description' => 'An orange version of the jackets that we sell.',
                'attributes' => [
                    [
                        '_id' => 'row-1',
                        'key' => 'Color',
                        'value' => 'Orange',
                    ],
                ],
                'price' => '400',
                'stock' => '50',
                'unlimited_stock' => false,
                'max_quantity' => 5,
                'product' => 'waterproof-jacket',
            ],
            [
                'sku' => 'WATJAKBLA',
                'title' => 'Black',
                'description' => 'An black version of the jackets that we sell.',
                'attributes' => [
                    [
                        '_id' => 'row-1',
                        'key' => 'Color',
                        'value' => 'Black',
                    ],
                ],
                'price' => '400',
                'stock' => '50',
                'unlimited_stock' => false,
                'max_quantity' => 5,
                'product' => 'waterproof-jacket',
            ],
            [
                'sku' => 'WATJAKGRE',
                'title' => 'Green',
                'description' => 'An green version of the jackets that we sell.',
                'attributes' => [
                    [
                        '_id' => 'row-1',
                        'key' => 'Color',
                        'value' => 'Green',
                    ],
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
            $item->name = $variant['name'];
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
