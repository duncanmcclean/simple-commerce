<?php

namespace Tests\Feature\Cart\Eloquent;

use DuncanMcClean\SimpleCommerce\Cart\Eloquent\CartModel;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Statamic;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\Feature\Cart\CartQueryTests;
use Tests\TestCase;

class CartTest extends TestCase
{
    use CartQueryTests, PreventsSavingStacheItemsToDisk, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic.simple-commerce.carts', [
            ...config('statamic.simple-commerce.carts'),
            'repository' => 'eloquent',
            'model' => \DuncanMcClean\SimpleCommerce\Cart\Eloquent\CartModel::class,
            'table' => 'carts',
        ]);

        $this->app->bind('simple-commerce.carts.eloquent.model', function () {
            return config('statamic.simple-commerce.carts.model', \DuncanMcClean\SimpleCommerce\Cart\Eloquent\CartModel::class);
        });

        $this->app->bind('simple-commerce.carts.eloquent.line_items_model', function () {
            return config('statamic.simple-commerce.carts.line_items_model', \DuncanMcClean\SimpleCommerce\Cart\Eloquent\LineItemModel::class);
        });

        Statamic::repository(
            \DuncanMcClean\SimpleCommerce\Contracts\Cart\CartRepository::class,
            \DuncanMcClean\SimpleCommerce\Cart\Eloquent\CartRepository::class
        );
    }

    #[Test]
    public function can_find_carts()
    {
        $model = CartModel::create([
            'site' => 'default',
            'customer' => json_encode(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov']),
            'grand_total' => 2500,
            'sub_total' => 2500,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'data' => ['foo' => 'bar'],
        ]);

        $model->lineItems()->create([
            'id' => '123',
            'product' => 'abc',
            'quantity' => 1,
            'unit_price' => 2500,
            'sub_total' => 2500,
            'tax_total' => 0,
            'total' => 2500,
        ]);

        $cart = Cart::find($model->id);

        $this->assertEquals($model->id, $cart->id());
        $this->assertEquals($model->site, $cart->site()->handle());
        $this->assertEquals(json_decode($model->customer, true)['name'], $cart->customer->name());
        $this->assertEquals(json_decode($model->customer, true)['email'], $cart->customer->email());
        $this->assertEquals($model->grand_total, $cart->grandTotal());
        $this->assertEquals($model->sub_total, $cart->subTotal());
        $this->assertEquals($model->discount_total, $cart->discountTotal());
        $this->assertEquals($model->tax_total, $cart->taxTotal());
        $this->assertEquals($model->shipping_total, $cart->shippingTotal());
        $this->assertEquals($model->data, $cart->data()->except('updated_at')->all());

        $this->assertEquals('123', $cart->lineItems()->first()->id());
        $this->assertEquals(2500, $cart->lineItems()->first()->total());
    }

    #[Test]
    public function can_save_a_cart()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('abc')->data(['price' => 2500])->save();

        $cart = Cart::make()
            ->site('default')
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov'])
            ->grandTotal(2500)
            ->subTotal(2500)
            ->discountTotal(0)
            ->taxTotal(0)
            ->shippingTotal(0)
            ->lineItems([['id' => '123', 'product' => 'abc', 'quantity' => 1, 'total' => 2500]])
            ->data($data = ['foo' => 'bar']);

        $save = $cart->save();

        $this->assertTrue($save);

        $this->assertDatabaseHas('carts', [
            'site' => 'default',
            'grand_total' => 2500,
            'data->foo' => 'bar',
        ]);

        $this->assertDatabaseHas('cart_line_items', [
            'cart_id' => $cart->id(),
            'product' => 'abc',
            'quantity' => 1,
            'total' => 2500,
        ]);

        $this->assertNotNull($cart->id());
    }

    #[Test]
    public function can_delete_a_cart()
    {
        $cart = Cart::make()
            ->id('123')
            ->site('default')
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov']);

        $cart->save();

        $delete = $cart->delete();

        $this->assertTrue($delete);

        $this->assertDatabaseMissing('orders', [
            'id' => '123',
            'site' => 'default',
        ]);
    }
}
