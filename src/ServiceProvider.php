<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Barryvdh\Debugbar\Facade as Debugbar;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use Illuminate\Foundation\Console\AboutCommand;
use Statamic\CP\Navigation\NavItem;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Facades\Collection;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Stache\Stache;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $config = false;
    protected $translations = false;

    protected $actions = [
        Actions\RefundAction::class,
        Actions\UpdateOrderStatus::class,
    ];

    protected $commands = [
        Console\Commands\CartCleanupCommand::class,
        Console\Commands\MakeGateway::class,
        Console\Commands\MakeShippingMethod::class,
        Console\Commands\InstallCommand::class,
        Console\Commands\MigrateOrdersToDatabase::class,
        Console\Commands\SwitchToDatabase::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\CountryFieldtype::class,
        Fieldtypes\CouponFieldtype::class,
        Fieldtypes\GatewayFieldtype::class,
        Fieldtypes\MoneyFieldtype::class,
        Fieldtypes\OrderStatusFieldtype::class,
        Fieldtypes\ProductVariantFieldtype::class,
        Fieldtypes\ProductVariantsFieldtype::class,
        Fieldtypes\RegionFieldtype::class,
        Fieldtypes\ShippingMethodFieldtype::class,
        Fieldtypes\TaxCategoryFieldtype::class,

        Fieldtypes\Variables\LineItemTax::class,
    ];

    protected $listen = [
        EntryBlueprintFound::class  => [
            Listeners\EnforceBlueprintFields::class,
            Listeners\AddHiddenFields::class,
        ],
        Events\PostCheckout::class => [
            Listeners\TidyTemporaryGatewayData::class,
        ],
        Events\OrderPaid::class => [
            Listeners\SendConfiguredNotifications::class,
        ],
        Events\OrderPaymentFailed::class => [
            Listeners\SendConfiguredNotifications::class,
        ],
        Events\OrderShipped::class => [
            Listeners\SendConfiguredNotifications::class,
        ],
        Events\StockRunningLow::class => [
            Listeners\SendConfiguredNotifications::class,
        ],
        Events\StockRunOut::class => [
            Listeners\SendConfiguredNotifications::class,
        ],
    ];

    protected $modifiers = [
        Modifiers\Currency::class,
    ];

    protected $routes = [
        'actions' => __DIR__ . '/../routes/actions.php',
        'cp'      => __DIR__ . '/../routes/cp.php',
    ];

    protected $stylesheets = [
        __DIR__ . '/../resources/dist/css/cp.css',
    ];

    protected $scripts = [
        __DIR__ . '/../resources/dist/js/cp.js',
    ];

    protected $scopes = [
        Scopes\OrderContainsProduct::class,
        Scopes\OrderCustomer::class,
        Scopes\OrderStatusFilter::class,
    ];

    protected $tags = [
        Tags\SimpleCommerceTag::class,
        Tags\TotalIncludingTax::class,
    ];

    protected $updateScripts = [
        UpdateScripts\v2_3\AddBlueprintFields::class,
        UpdateScripts\v2_3\MigrateConfig::class,
        UpdateScripts\v2_3\MigrateLineItemMetadata::class,

        UpdateScripts\v2_4\AddTaxFieldToOrderLineItems::class,
        // UpdateScripts\v2_4\MigrateGatewayDataToNewFormat::class,
        UpdateScripts\v2_4\MigrateSingleCartConfig::class,
        UpdateScripts\v2_4\MigrateTaxConfiguration::class,

        UpdateScripts\v3_0\AddNewFieldsToOrderBlueprint::class,
        UpdateScripts\v3_0\ConfigureTitleFormats::class,
        UpdateScripts\v3_0\ConfigureWhitelistedFields::class,
        UpdateScripts\v3_0\UpdateContentRepositoryReferences::class,

        UpdateScripts\v4_0\MigrateCouponsToStache::class,
    ];

    public function boot()
    {
        parent::boot();

        Statamic::booted(function () {
            $this
                ->bootVendorAssets()
                ->bindContracts()
                ->bootCartDrivers();
        });

        SimpleCommerce::bootGateways();
        SimpleCommerce::bootTaxEngine();
        SimpleCommerce::bootShippingMethods();

        Overview::bootCoreWidgets();

        Statamic::booted(function () {
            $this
                ->bootStacheStores()
                ->createNavItems()
                ->registerPermissions()
                ->registerComputedValues();
        });

        if (class_exists('Barryvdh\Debugbar\ServiceProvider') && config('debugbar.enabled', false) === true) {
            Debugbar::addCollector(new DebugbarDataCollector('simple-commerce'));
        }

        AboutCommand::add('Simple Commerce', function () {
            return [
                'Repository: Customer' => SimpleCommerce::customerDriver()['repository'],
                'Repository: Order' => SimpleCommerce::orderDriver()['repository'],
                'Repository: Product' => SimpleCommerce::productDriver()['repository'],
                'Gateways' => collect(SimpleCommerce::gateways())->pluck('name')->implode(', '),
                'Shipping Methods' => SimpleCommerce::shippingMethods()->pluck('name')->implode(', '),
                'Tax Engine' => get_class(SimpleCommerce::taxEngine()),
            ];
        });
    }

    protected function bootVendorAssets()
    {
        $this->publishes([
            __DIR__ . '/../resources/dist' => public_path('vendor/simple-commerce'),
        ], 'simple-commerce');

        $this->publishes([
            __DIR__ . '/../config/simple-commerce.php' => config_path('simple-commerce.php'),
        ], 'simple-commerce-config');

        $this->publishes([
            __DIR__ . '/../resources/blueprints' => resource_path('blueprints'),
        ], 'simple-commerce-blueprints');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/simple-commerce'),
        ], 'simple-commerce-translations');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/simple-commerce'),
        ], 'simple-commerce-views');

        if (app()->environment() !== 'testing') {
            $this->mergeConfigFrom(__DIR__ . '/../config/simple-commerce.php', 'simple-commerce');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'simple-commerce');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'simple-commerce');

        return $this;
    }

    protected function bindContracts()
    {
        $bindings = [
            Contracts\GatewayManager::class     => Gateways\Manager::class,
            Contracts\ShippingManager::class    => Shipping\Manager::class,
            Contracts\Calculator::class         => Orders\Calculator::class,
        ];

        if (isset(SimpleCommerce::customerDriver()['repository'])) {
            $bindings[Contracts\CustomerRepository::class] = SimpleCommerce::customerDriver()['repository'];
        }

        if (isset(SimpleCommerce::orderDriver()['repository'])) {
            $bindings[Contracts\OrderRepository::class] = SimpleCommerce::orderDriver()['repository'];
        }

        if (isset(SimpleCommerce::productDriver()['repository'])) {
            $bindings[Contracts\ProductRepository::class] = SimpleCommerce::productDriver()['repository'];
        }

        collect($bindings)->each(function ($concrete, $abstract) {
            if (! $this->app->bound($abstract)) {
                Statamic::repository($abstract, $concrete);
            }
        });

        $this->app->bind(Contracts\Order::class, Orders\Order::class);
        $this->app->bind(Contracts\Coupon::class, Coupons\Coupon::class);
        $this->app->bind(Contracts\Customer::class, Customers\Customer::class);
        $this->app->bind(Contracts\Product::class, Products\Product::class);

        return $this;
    }

    protected function bootCartDrivers()
    {
        if (! $this->app->bound(Contracts\CartDriver::class)) {
            $this->app->bind(
                Contracts\CartDriver::class,
                config('simple-commerce.cart.driver', \DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CookieDriver::class)
            );
        }

        return $this;
    }

    protected function bootStacheStores()
    {
        $couponStore = new Coupons\CouponStore;
        $couponStore->directory(base_path('content/simple-commerce/coupons'));

        app(Stache::class)->registerStore($couponStore);

        $this->app->bind(Contracts\CouponRepository::class, function () {
            return new Coupons\CouponRepository(app('stache'));
        });

        if (SimpleCommerce::isUsingStandardTaxEngine()) {
            $taxCategoryStore = new Tax\Standard\Stache\TaxCategory\TaxCategoryStore;
            $taxCategoryStore->directory(base_path('content/simple-commerce/tax-categories'));

            $taxRateStore = new Tax\Standard\Stache\TaxRate\TaxRateStore;
            $taxRateStore->directory(base_path('content/simple-commerce/tax-rates'));

            $taxZoneStore = new Tax\Standard\Stache\TaxZone\TaxZoneStore;
            $taxZoneStore->directory(base_path('content/simple-commerce/tax-zones'));

            app(Stache::class)->registerStore($taxCategoryStore);
            app(Stache::class)->registerStore($taxRateStore);
            app(Stache::class)->registerStore($taxZoneStore);

            $this->app->bind(Contracts\TaxCategoryRepository::class, function () {
                return new Tax\Standard\Stache\TaxCategory\TaxCategoryRepository(app('stache'));
            });

            $this->app->bind(Contracts\TaxRateRepository::class, function () {
                return new Tax\Standard\Stache\TaxRate\TaxRateRepository(app('stache'));
            });

            $this->app->bind(Contracts\TaxZoneRepository::class, function () {
                return new Tax\Standard\Stache\TaxZone\TaxZoneRepository(app('stache'));
            });
        }

        return $this;
    }

    protected function createNavItems()
    {
        Nav::extend(function ($nav) {
            $nav->create(__('Overview'))
                ->section(__('Simple Commerce'))
                ->route('simple-commerce.overview')
                ->can('view simple commerce overview')
                ->icon('charts');

            if (
                $this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], \DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository::class)
            ) {
                $nav->create(__('Orders'))
                    ->section(__('Simple Commerce'))
                    ->route('collections.show', SimpleCommerce::orderDriver()['collection'])
                    ->can('view', Collection::find(SimpleCommerce::orderDriver()['collection']))
                    ->icon(SimpleCommerce::svg('shop'));
            } elseif (
                class_exists('DoubleThreeDigital\Runway\Runway') &&
                $this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], \DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository::class)
            ) {
                $orderModelClass = SimpleCommerce::orderDriver()['model'];
                $orderResource = \DoubleThreeDigital\Runway\Runway::findResourceByModel(new $orderModelClass);

                $nav->create(__('Orders'))
                    ->section(__('Simple Commerce'))
                    ->route('runway.index', ['resourceHandle' => $orderResource->handle()])
                    ->can("View {$orderResource->plural()}")
                    ->icon(SimpleCommerce::svg('shop'));
            }

            if (
                $this->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], \DoubleThreeDigital\SimpleCommerce\Customers\EntryCustomerRepository::class)
            ) {
                $nav->create(__('Customers'))
                    ->section(__('Simple Commerce'))
                    ->route('collections.show', SimpleCommerce::customerDriver()['collection'])
                    ->can('view', Collection::find(SimpleCommerce::customerDriver()['collection']))
                    ->icon('user');
            } elseif (
                $this->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], \DoubleThreeDigital\SimpleCommerce\Customers\UserCustomerRepository::class)
            ) {
                $nav->create(__('Customers'))
                    ->section(__('Simple Commerce'))
                    ->route('users.index')
                    ->can('index', \Statamic\Contracts\Auth\User::class)
                    ->icon('user');
            } elseif (
                class_exists('DoubleThreeDigital\Runway\Runway') &&
                $this->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], \DoubleThreeDigital\SimpleCommerce\Customers\EloquentCustomerRepository::class)
            ) {
                $customerModelClass = SimpleCommerce::customerDriver()['model'];
                $customerResource = \DoubleThreeDigital\Runway\Runway::findResourceByModel(new $customerModelClass);

                $nav->create(__('Customers'))
                    ->section(__('Simple Commerce'))
                    ->route('runway.index', ['resourceHandle' => $customerResource->handle()])
                    ->can("View {$customerResource->plural()}")
                    ->icon('user');
            }

            $nav->create(__('Products'))
                ->section(__('Simple Commerce'))
                ->route('collections.show', SimpleCommerce::productDriver()['collection'])
                ->can('view', Collection::find(SimpleCommerce::productDriver()['collection']))
                ->icon('entries');

            $nav->create(__('Coupons'))
                ->section(__('Simple Commerce'))
                ->route('simple-commerce.coupons.index')
                ->can('view coupons')
                ->icon('tags');

            if (SimpleCommerce::isUsingStandardTaxEngine()) {
                $nav->create(__('Tax'))
                    ->section(__('Simple Commerce'))
                    ->route('simple-commerce.tax')
                    ->can('view tax rates')
                    ->icon(SimpleCommerce::svg('money-cash-file-dollar'));
            }

            // Drop any collection items from 'Collections' nav
            $collectionsNavItem = collect($nav->items())->first(function (NavItem $navItem) {
                return $navItem->url() === cp_route('collections.index');
            });

            if ($collectionsNavItem && $collectionsNavItem->children()) {
                $children = $collectionsNavItem->children()()
                    ->reject(function ($child) {
                        return in_array(
                            $child->name(),
                            collect(config('simple-commerce.content'))
                                ->pluck('collection')
                                ->filter()
                                ->reject(function ($collectionHandle) {
                                    return is_null(Collection::find($collectionHandle));
                                })
                                ->map(function ($collectionHandle) {
                                    return __(Collection::find($collectionHandle)->title());
                                })
                                ->toArray(),
                        );
                    });

                $collectionsNavItem->children(function () use ($children) {
                    return $children;
                });
            }
        });

        return $this;
    }

    protected function registerPermissions()
    {
        Permission::register('view simple commerce overview')
            ->label('View Simple Commerce Overview');

        Permission::register('view coupons', function ($permission) {
            $permission->children([
                Permission::make('edit coupons')->children([
                    Permission::make('create coupons'),
                    Permission::make('delete coupons'),
                ]),
            ]);
        });

        if (SimpleCommerce::isUsingStandardTaxEngine()) {
            Permission::register('view tax rates', function ($permission) {
                $permission->children([
                    Permission::make('edit tax rates')->children([
                        Permission::make('create tax rates'),
                        Permission::make('delete tax rates'),
                    ]),
                ]);
            });

            Permission::register('view tax categories', function ($permission) {
                $permission->children([
                    Permission::make('edit tax categories')->children([
                        Permission::make('create tax categories'),
                        Permission::make('delete tax categories'),
                    ]),
                ]);
            });

            Permission::register('view tax zones', function ($permission) {
                $permission->children([
                    Permission::make('edit tax zones')->children([
                        Permission::make('create tax zones'),
                        Permission::make('delete tax zones'),
                    ]),
                ]);
            });
        }

        return $this;
    }

    protected function registerComputedValues()
    {
        if (
            $this->isOrExtendsClass(SimpleCommerce::productDriver()['repository'], \DoubleThreeDigital\SimpleCommerce\Products\EntryProductRepository::class)
        ) {
            Collection::computed(SimpleCommerce::productDriver()['collection'], 'raw_price', function ($entry, $value) {
                return $entry->get('price');
            });
        }
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
