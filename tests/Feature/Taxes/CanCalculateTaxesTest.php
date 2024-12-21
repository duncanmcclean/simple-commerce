<?php

namespace Feature\Taxes;

use DuncanMcClean\SimpleCommerce\Cart\Calculator\CalculateTaxes;
use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\TaxClass;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use DuncanMcClean\SimpleCommerce\Shipping\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Shipping\ShippingOption;
use DuncanMcClean\SimpleCommerce\Taxes\TaxCalculation;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CanCalculateTaxesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        PaidShipping::register();

        Collection::make('products')->save();

        TaxClass::make()->handle('standard')->set('name', 'Standard')->save();

        $path = base_path('content/simple-commerce/tax-zones.yaml');

        File::delete($path);
        File::ensureDirectoryExists(Str::beforeLast($path, '/'));

        config()->set('statamic.simple-commerce.taxes', [
            'price_includes_tax' => false,
            'shipping_tax_behaviour' => 'tax_class',
        ]);
    }

    #[Test]
    public function tax_totals_are_zero_when_no_tax_zone_is_available()
    {
        $product = Entry::make()->collection('products')->data(['price' => 10000, 'tax_class' => 'standard']);
        $product->save();

        $cart = Cart::make()
            ->lineItems([
                ['id' => 'one', 'product' => $product->id(), 'quantity' => 1, 'total' => 10000],
            ])
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'GBR',
                'shipping_state' => 'GLG',
            ]);

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->find('one');

        $this->assertEquals([], $lineItem->get('tax_breakdown'));

        $this->assertEquals(0, $lineItem->taxTotal());
        $this->assertEquals(10000, $lineItem->total());
        $this->assertEquals(0, $cart->taxTotal());
    }

    #[Test]
    public function tax_totals_are_zero_when_no_tax_rate_is_available_for_tax_class()
    {
        $product = Entry::make()->collection('products')->data(['price' => 10000, 'tax_class' => 'standard']);
        $product->save();

        $cart = Cart::make()
            ->lineItems([
                ['id' => 'one', 'product' => $product->id(), 'quantity' => 1, 'total' => 10000],
            ])
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'GBR',
                'shipping_state' => 'GLG',
            ]);

        TaxZone::make()->handle('uk')->data([
            'type' => 'countries',
            'countries' => ['GBR'],
            'rates' => [],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->find('one');

        $this->assertEquals([], $lineItem->get('tax_breakdown'));

        $this->assertEquals(0, $lineItem->taxTotal());
        $this->assertEquals(10000, $lineItem->total());
        $this->assertEquals(0, $cart->taxTotal());
    }

    #[Test]
    public function calculates_line_item_tax_when_price_includes_tax()
    {
        config(['statamic.simple-commerce.taxes.price_includes_tax' => true]);

        $product = Entry::make()->collection('products')->data(['price' => 10000, 'tax_class' => 'standard']);
        $product->save();

        $cart = Cart::make()
            ->lineItems([
                ['id' => 'one', 'product' => $product->id(), 'quantity' => 1, 'total' => 10000],
            ])
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
            ]);

        TaxZone::make()->handle('usa')->data([
            'name' => 'USA',
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['standard' => 20],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->find('one');

        $this->assertEquals([
            ['rate' => 20, 'description' => 'Standard', 'zone' => 'USA', 'amount' => 1667],
        ], $lineItem->get('tax_breakdown'));

        $this->assertEquals(1667, $lineItem->taxTotal());
        $this->assertEquals(10000, $lineItem->total());
        $this->assertEquals(1667, $cart->taxTotal());
    }

    #[Test]
    public function calculates_line_item_tax_when_price_excludes_tax()
    {
        $product = Entry::make()->collection('products')->data(['price' => 10000, 'tax_class' => 'standard']);
        $product->save();

        $cart = Cart::make()
            ->lineItems([
                ['id' => 'one', 'product' => $product->id(), 'quantity' => 1, 'total' => 10000],
            ])
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
            ]);

        TaxZone::make()->handle('usa')->data([
            'name' => 'USA',
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['standard' => 20],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->find('one');

        $this->assertEquals([
            ['rate' => 20, 'description' => 'Standard', 'zone' => 'USA', 'amount' => 2000],
        ], $lineItem->get('tax_breakdown'));

        $this->assertEquals(2000, $lineItem->taxTotal());
        $this->assertEquals(12000, $lineItem->total());
        $this->assertEquals(2000, $cart->taxTotal());
    }

    #[Test]
    public function calculates_line_item_tax_when_discount_is_applied()
    {
        $coupon = tap(Coupon::make()->code('foobar')->type(CouponType::Fixed)->amount(500))->save();

        $product = Entry::make()->collection('products')->data(['price' => 2500, 'tax_class' => 'standard']);
        $product->save();

        $cart = Cart::make()
            ->coupon($coupon->id())
            ->lineItems([
                ['id' => 'one', 'product' => $product->id(), 'quantity' => 1, 'total' => 2500, 'discount_amount' => 500],
            ])
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
            ]);

        TaxZone::make()->handle('usa')->data([
            'name' => 'USA',
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['standard' => 20],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->find('one');

        $this->assertEquals([
            ['rate' => 20, 'description' => 'Standard', 'zone' => 'USA', 'amount' => 400],
        ], $lineItem->get('tax_breakdown'));

        // Tax Total should be calculated based on the total *after* the discount has been applied.
        $this->assertEquals(400, $lineItem->taxTotal());
        $this->assertEquals(2400, $lineItem->total());
        $this->assertEquals(400, $cart->taxTotal());
    }

    #[Test]
    public function calculates_line_item_tax_with_multiple_quantities()
    {
        $product = Entry::make()->collection('products')->data(['price' => 10000, 'tax_class' => 'standard']);
        $product->save();

        $cart = Cart::make()
            ->lineItems([
                ['id' => 'one', 'product' => $product->id(), 'quantity' => 5, 'total' => 50000],
            ])
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
            ]);

        TaxZone::make()->handle('usa')->data([
            'name' => 'USA',
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['standard' => 20],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->find('one');

        $this->assertEquals([
            ['rate' => 20, 'description' => 'Standard', 'zone' => 'USA', 'amount' => 10000],
        ], $lineItem->get('tax_breakdown'));

        $this->assertEquals(10000, $lineItem->taxTotal());
        $this->assertEquals(60000, $lineItem->total());
        $this->assertEquals(10000, $cart->taxTotal());
    }

    #[Test]
    public function calculates_line_item_tax_using_multiple_tax_rates()
    {
        $product = Entry::make()->collection('products')->data(['price' => 10000, 'tax_class' => 'standard']);
        $product->save();

        $cart = Cart::make()
            ->lineItems([
                ['id' => 'one', 'product' => $product->id(), 'quantity' => 1, 'total' => 10000],
            ])
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
            ]);

        TaxZone::make()->handle('usa')->data([
            'name' => 'USA',
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['standard' => 20],
        ])->save();

        TaxZone::make()->handle('california')->data([
            'name' => 'California',
            'type' => 'states',
            'countries' => ['USA'],
            'states' => ['CA'],
            'rates' => ['standard' => 5],
        ])->save();

        TaxZone::make()->handle('ca_fa')->data([
            'name' => 'CA FA',
            'type' => 'postcodes',
            'countries' => ['USA'],
            'postcodes' => ['FA*'],
            'rates' => ['standard' => 2],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->find('one');

        $this->assertEquals([
            ['rate' => 20, 'description' => 'Standard', 'zone' => 'USA', 'amount' => 2000],
            ['rate' => 5, 'description' => 'Standard', 'zone' => 'California', 'amount' => 500],
            ['rate' => 2, 'description' => 'Standard', 'zone' => 'CA FA', 'amount' => 200],
        ], $lineItem->get('tax_breakdown'));

        $this->assertEquals(2700, $lineItem->taxTotal());
        $this->assertEquals(12700, $lineItem->total());
        $this->assertEquals(2700, $cart->taxTotal());
    }

    #[Test]
    public function calculate_line_item_tax_when_rate_is_a_floating_point_number()
    {
        $product = Entry::make()->collection('products')->data(['price' => 10000, 'tax_class' => 'standard']);
        $product->save();

        $cart = Cart::make()
            ->lineItems([
                ['id' => 'one', 'product' => $product->id(), 'quantity' => 1, 'total' => 10000],
            ])
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
            ]);

        TaxZone::make()->handle('usa')->data([
            'name' => 'USA',
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['standard' => 25.5],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->find('one');

        $this->assertEquals([
            ['rate' => 25.5, 'description' => 'Standard', 'zone' => 'USA', 'amount' => 2550],
        ], $lineItem->get('tax_breakdown'));

        $this->assertEquals(2550, $lineItem->taxTotal());
        $this->assertEquals(12550, $lineItem->total());
        $this->assertEquals(2550, $cart->taxTotal());
    }

    #[Test]
    public function calculates_tax_for_multiple_line_items()
    {
        TaxClass::make()->handle('reduced')->set('name', 'Reduced')->save();

        $productA = Entry::make()->collection('products')->data(['price' => 10000, 'tax_class' => 'standard']);
        $productA->save();

        $productB = Entry::make()->collection('products')->data(['price' => 5000, 'tax_class' => 'reduced']);
        $productB->save();

        $cart = Cart::make()
            ->lineItems([
                ['id' => 'one', 'product' => $productA->id(), 'quantity' => 1, 'total' => 10000],
                ['id' => 'two', 'product' => $productB->id(), 'quantity' => 1, 'total' => 5000],
            ])
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
            ]);

        TaxZone::make()->handle('usa')->data([
            'name' => 'USA',
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['standard' => 20, 'reduced' => 15],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        // Standard: 20% tax
        $lineItemOne = $cart->lineItems()->find('one');

        $this->assertEquals([
            ['rate' => 20, 'description' => 'Standard', 'zone' => 'USA', 'amount' => 2000],
        ], $lineItemOne->get('tax_breakdown'));

        $this->assertEquals(2000, $lineItemOne->taxTotal());
        $this->assertEquals(12000, $lineItemOne->total());

        // Reduced: 15% tax
        $lineItemTwo = $cart->lineItems()->find('two');

        $this->assertEquals([
            ['rate' => 15, 'description' => 'Reduced', 'zone' => 'USA', 'amount' => 750],
        ], $lineItemTwo->get('tax_breakdown'));

        $this->assertEquals(750, $lineItemTwo->taxTotal());
        $this->assertEquals(5750, $lineItemTwo->total());

        $this->assertEquals(2750, $cart->taxTotal());
    }

    #[Test]
    public function calculates_line_item_tax_for_variant_products()
    {
        $product = Entry::make()->collection('products')->data([
            'tax_class' => 'standard',
            'product_variants' => [
                'variants' => [['name' => 'Colour', 'values' => ['Red']]],
                'options' => [['key' => 'Red', 'variant' => 'Red', 'price' => 10000]],
            ],
        ]);
        $product->save();

        $cart = Cart::make()
            ->lineItems([
                ['id' => 'one', 'product' => $product->id(), 'variant' => 'Red', 'quantity' => 1, 'total' => 10000],
            ])
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
            ]);

        TaxZone::make()->handle('usa')->data([
            'name' => 'USA',
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['standard' => 20],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->find('one');

        $this->assertEquals([
            ['rate' => 20, 'description' => 'Standard', 'zone' => 'USA', 'amount' => 2000],
        ], $lineItem->get('tax_breakdown'));

        $this->assertEquals(2000, $lineItem->taxTotal());
        $this->assertEquals(12000, $lineItem->total());
        $this->assertEquals(2000, $cart->taxTotal());
    }

    #[Test]
    public function calculates_shipping_tax_when_price_includes_tax()
    {
        config(['statamic.simple-commerce.taxes.price_includes_tax' => true]);

        $cart = Cart::make()
            ->shippingTotal(500)
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
                'shipping_method' => 'paid_shipping',
                'shipping_option' => 'the_only_option',
            ]);

        TaxZone::make()->handle('usa')->data([
            'name' => 'USA',
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['shipping' => 20],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals([
            ['rate' => 20, 'description' => 'Shipping', 'zone' => 'USA', 'amount' => 83],
        ], $cart->get('shipping_tax_breakdown'));

        $this->assertEquals(83, $cart->get('shipping_tax_total'));
        $this->assertEquals(500, $cart->shippingTotal());
        $this->assertEquals(83, $cart->taxTotal());
    }

    #[Test]
    public function calculates_shipping_tax_class_when_price_excludes_tax()
    {
        $cart = Cart::make()
            ->shippingTotal(500)
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
                'shipping_method' => 'paid_shipping',
                'shipping_option' => 'the_only_option',
            ]);

        TaxZone::make()->handle('usa')->data([
            'name' => 'USA',
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['shipping' => 20],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals([
            ['rate' => 20, 'description' => 'Shipping', 'zone' => 'USA', 'amount' => 100],
        ], $cart->get('shipping_tax_breakdown'));

        $this->assertEquals(100, $cart->get('shipping_tax_total'));
        $this->assertEquals(600, $cart->shippingTotal());
        $this->assertEquals(100, $cart->taxTotal());
    }

    #[Test]
    public function calculates_shipping_tax_class_using_multiple_tax_rates()
    {
        $cart = Cart::make()
            ->shippingTotal(500)
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
                'shipping_method' => 'paid_shipping',
                'shipping_option' => 'the_only_option',
            ]);

        TaxZone::make()->handle('usa')->data([
            'name' => 'USA',
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['shipping' => 20],
        ])->save();

        TaxZone::make()->handle('california')->data([
            'name' => 'California',
            'type' => 'states',
            'countries' => ['USA'],
            'states' => ['CA'],
            'rates' => ['shipping' => 5],
        ])->save();

        TaxZone::make()->handle('ca_fa')->data([
            'name' => 'CA FA',
            'type' => 'postcodes',
            'countries' => ['USA'],
            'postcodes' => ['FA*'],
            'rates' => ['shipping' => 2],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals([
            ['rate' => 20, 'description' => 'Shipping', 'zone' => 'USA', 'amount' => 100],
            ['rate' => 5, 'description' => 'Shipping', 'zone' => 'California', 'amount' => 25],
            ['rate' => 2, 'description' => 'Shipping', 'zone' => 'CA FA', 'amount' => 10],
        ], $cart->get('shipping_tax_breakdown'));

        $this->assertEquals(135, $cart->get('shipping_tax_total'));
        $this->assertEquals(635, $cart->shippingTotal());
        $this->assertEquals(135, $cart->taxTotal());
    }

    #[Test]
    public function calculates_shipping_tax_class_when_tax_rate_is_a_floating_point_number()
    {
        $cart = Cart::make()
            ->shippingTotal(500)
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'USA',
                'shipping_state' => 'CA',
                'shipping_method' => 'paid_shipping',
                'shipping_option' => 'the_only_option',
            ]);

        TaxZone::make()->handle('usa')->data([
            'name' => 'USA',
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['shipping' => 25.5],
        ])->save();

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals([
            ['rate' => 25.5, 'description' => 'Shipping', 'zone' => 'USA', 'amount' => 128],
        ], $cart->get('shipping_tax_breakdown'));

        $this->assertEquals(128, $cart->get('shipping_tax_total'));
        $this->assertEquals(628, $cart->shippingTotal());
        $this->assertEquals(128, $cart->taxTotal());
    }

    #[Test]
    public function uses_custom_tax_driver()
    {
        $taxDriver = new class implements \DuncanMcClean\SimpleCommerce\Contracts\Taxes\Driver {
            public $lineItem;

            public function setAddress($address): self
            {
                return $this;
            }

            public function setPurchasable($purchasable): self
            {
                return $this;
            }

            public function setLineItem($lineItem): self
            {
                $this->lineItem = $lineItem;

                return $this;
            }

            public function getBreakdown(int $total): \Illuminate\Support\Collection
            {
                if ($this->lineItem) {
                    $this->lineItem = null;

                    return collect([
                        TaxCalculation::make(rate: 10, description: 'Line Item Tax', zone: 'Custom', amount: 1000),
                    ]);
                }

                return collect([
                    TaxCalculation::make(rate: 50, description: 'Shipping Tax', zone: 'Custom', amount: 250),
                ]);
            }
        };

        app()->instance(\DuncanMcClean\SimpleCommerce\Contracts\Taxes\Driver::class, $taxDriver);

        $product = Entry::make()->collection('products')->data(['price' => 10000]);
        $product->save();

        $cart = Cart::make()
            ->shippingTotal(500)
            ->lineItems([
                ['id' => 'one', 'product' => $product->id(), 'quantity' => 1, 'total' => 10000],
            ])
            ->data([
                'shipping_method' => 'paid_shipping',
                'shipping_option' => 'the_only_option',
            ]);

        $cart = app(CalculateTaxes::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->find('one');

        $this->assertEquals([
            ['rate' => 10, 'description' => 'Line Item Tax', 'zone' => 'Custom', 'amount' => 1000],
        ], $lineItem->get('tax_breakdown'));

        $this->assertEquals(1000, $lineItem->taxTotal());
        $this->assertEquals(11000, $lineItem->total());

        $this->assertEquals([
            ['rate' => 50, 'description' => 'Shipping Tax', 'zone' => 'Custom', 'amount' => 250],
        ], $cart->get('shipping_tax_breakdown'));

        $this->assertEquals(250, $cart->get('shipping_tax_total'));
        $this->assertEquals(750, $cart->shippingTotal());

        $this->assertEquals(1250, $cart->taxTotal());
    }
}

class PaidShipping extends ShippingMethod
{
    public function options(CartContract $cart): \Illuminate\Support\Collection
    {
        return collect([
            ShippingOption::make($this)
                ->name('The Only Option')
                ->price(500),
        ]);
    }
}