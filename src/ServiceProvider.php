<?php

namespace DuncanMcClean\SimpleCommerce;

use Barryvdh\Debugbar\Facade as Debugbar;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Support\Runway;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Carbon;
use Statamic\CP\Navigation\NavItem;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Events\UserBlueprintFound;
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
        Actions\Delete::class,
        Actions\RefundAction::class,
        Actions\UpdateOrderStatus::class,
    ];

    protected $commands = [
        Console\Commands\PurgeCartOrdersCommand::class,
        Console\Commands\MakeGateway::class,
        Console\Commands\MakeShippingMethod::class,
        Console\Commands\InstallCommand::class,
        Console\Commands\MigrateOrderStatuses::class,
        Console\Commands\MigrateOrdersToDatabase::class,
        Console\Commands\RunUpdateScripts::class,
        Console\Commands\SwitchToDatabase::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\CountryFieldtype::class,
        Fieldtypes\CouponCodeFieldtype::class,
        Fieldtypes\CouponFieldtype::class,
        Fieldtypes\CouponSummaryFieldtype::class,
        Fieldtypes\CouponValueFieldtype::class,
        Fieldtypes\GatewayFieldtype::class,
        Fieldtypes\MoneyFieldtype::class,
        Fieldtypes\OrderStatusFieldtype::class,
        Fieldtypes\PaymentStatusFieldtype::class,
        Fieldtypes\ProductVariantFieldtype::class,
        Fieldtypes\ProductVariantsFieldtype::class,
        Fieldtypes\RegionFieldtype::class,
        Fieldtypes\ShippingMethodFieldtype::class,
        Fieldtypes\StatusLogFieldtype::class,
        Fieldtypes\TaxCategoryFieldtype::class,

        Fieldtypes\Variables\LineItemTax::class,
    ];

    protected $listen = [
        EntryBlueprintFound::class => [
            Listeners\EnforceEntryBlueprintFields::class,
            Listeners\AddHiddenFields::class,
        ],
        UserBlueprintFound::class => [
            Listeners\EnforceUserBlueprintFields::class,
        ],
        Events\PostCheckout::class => [
            Listeners\TidyTemporaryGatewayData::class,
        ],
        Events\OrderStatusUpdated::class => [
            Listeners\SendConfiguredNotifications::class,
        ],
        Events\PaymentStatusUpdated::class => [
            Listeners\SendConfiguredNotifications::class,
        ],
        Events\OrderPaymentFailed::class => [
            Listeners\SendConfiguredNotifications::class,
        ],
        Events\StockRunningLow::class => [
            Listeners\SendConfiguredNotifications::class,
        ],
        Events\StockRunOut::class => [
            Listeners\SendConfiguredNotifications::class,
        ],
        Events\DigitalDownloadReady::class => [
            Listeners\SendConfiguredNotifications::class,
        ],
        \Illuminate\Auth\Events\Logout::class => [
            Listeners\RemoveCustomerFromOrder::class,
        ],
    ];

    protected $modifiers = [
        Modifiers\Currency::class,
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $scopes = [
        Query\Scopes\CouponTypeFilter::class,
        Query\Scopes\OrderContainsProduct::class,
        Query\Scopes\OrderCustomer::class,
        Query\Scopes\OrderStatusFilter::class,
        Query\Scopes\PaymentStatusFilter::class,
    ];

    protected $tags = [
        Tags\SimpleCommerceTag::class,
        Tags\TotalIncludingTax::class,
    ];

    protected $updateScripts = [
        UpdateScripts\v6_0\MigrateProductType::class,
        UpdateScripts\v6_0\PublishMigrations::class,
        UpdateScripts\v6_0\UpdateClassReferences::class,
        UpdateScripts\v6_0\UpdateCouponExpiryDate::class,
    ];

    protected $vite = [
        'hotFile' => 'vendor/simple-commerce/hot',
        'publicDirectory' => 'dist',
        'input' => [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ],
    ];

    protected $widgets = [
        Widgets\LowStockProducts::class,
        Widgets\OrdersChart::class,
        Widgets\RecentOrders::class,
        Widgets\TopCustomers::class,
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

        Statamic::booted(function () {
            $this
                ->bootStacheStores()
                ->createNavItems()
                ->registerPermissions()
                ->registerComputedValues();

            if (! app()->environment('testing')) {
                Telemetry::send();
            }
        });

        if (class_exists('Barryvdh\Debugbar\ServiceProvider') && config('debugbar.enabled', false) === true) {
            Debugbar::addCollector(new DebugbarDataCollector('simple-commerce'));
        }

        AboutCommand::add('Simple Commerce', function () {
            return [
                'Currencies' => collect(config('simple-commerce.sites'))->pluck('currency')->implode(', '),
                'Repository: Customer' => SimpleCommerce::customerDriver()['repository'],
                'Repository: Order' => SimpleCommerce::orderDriver()['repository'],
                'Repository: Product' => SimpleCommerce::productDriver()['repository'],
                'Gateways' => SimpleCommerce::gateways()->pluck('name')->implode(', '),
                'Shipping Methods' => SimpleCommerce::shippingMethods()->pluck('name')->implode(', '),
                'Tax Engine' => get_class(SimpleCommerce::taxEngine()),
            ];
        });
    }

    protected function bootBlueprints()
    {
        return $this;
    }

    protected function bootVendorAssets()
    {
        $this->publishes([
            __DIR__.'/../dist' => public_path('vendor/simple-commerce'),
        ], 'simple-commerce');

        $this->publishes([
            __DIR__.'/../config/simple-commerce.php' => config_path('simple-commerce.php'),
        ], 'simple-commerce-config');

        $this->publishes([
            __DIR__.'/../resources/blueprints' => resource_path('blueprints'),
        ], 'simple-commerce-blueprints');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/simple-commerce'),
        ], 'simple-commerce-translations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/simple-commerce'),
        ], 'simple-commerce-views');

        if (app()->environment() !== 'testing') {
            $this->mergeConfigFrom(__DIR__.'/../config/simple-commerce.php', 'simple-commerce');
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'simple-commerce');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'simple-commerce');

        return $this;
    }

    protected function bindContracts()
    {
        $bindings = [
            Contracts\LicenseKeyRepository::class => Products\DigitalProducts\LicenseKeyRepository::class,
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

        foreach ($bindings as $contract => $implementation) {
            $this->app->booted(function () use ($contract, $implementation) {
                Statamic::repository($contract, $implementation);
            });
        }

        $this->app->bind(Contracts\GatewayManager::class, Gateways\Manager::class);
        $this->app->bind(Contracts\ShippingManager::class, Shipping\Manager::class);

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
                config('simple-commerce.cart.driver', \DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CookieDriver::class)
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
            if (
                $this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], \DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository::class)
            ) {
                $nav->create(__('Orders'))
                    ->section(__('Simple Commerce'))
                    ->route('collections.show', SimpleCommerce::orderDriver()['collection'])
                    ->can('view', Collection::find(SimpleCommerce::orderDriver()['collection']))
                    ->icon(SimpleCommerce::svg('shop'));
            } elseif (
                class_exists('StatamicRadPack\Runway\Runway') &&
                $this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], \DuncanMcClean\SimpleCommerce\Orders\EloquentOrderRepository::class)
            ) {
                $orderResource = Runway::orderModel();

                $nav->create(__('Orders'))
                    ->section(__('Simple Commerce'))
                    ->route('runway.index', ['resource' => $orderResource->handle()])
                    ->can('view', $orderResource)
                    ->icon(SimpleCommerce::svg('shop'));
            }

            if (
                $this->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], \DuncanMcClean\SimpleCommerce\Customers\EntryCustomerRepository::class)
            ) {
                $nav->create(__('Customers'))
                    ->section(__('Simple Commerce'))
                    ->route('collections.show', SimpleCommerce::customerDriver()['collection'])
                    ->can('view', Collection::find(SimpleCommerce::customerDriver()['collection']))
                    ->icon('user');
            } elseif (
                $this->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], \DuncanMcClean\SimpleCommerce\Customers\UserCustomerRepository::class)
            ) {
                $nav->create(__('Customers'))
                    ->section(__('Simple Commerce'))
                    ->route('users.index')
                    ->can('index', \Statamic\Contracts\Auth\User::class)
                    ->icon('user');
            } elseif (
                class_exists('StatamicRadPack\Runway\Runway') &&
                $this->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], \DuncanMcClean\SimpleCommerce\Customers\EloquentCustomerRepository::class)
            ) {
                $customerResource = Runway::customerModel();

                $nav->create(__('Customers'))
                    ->section(__('Simple Commerce'))
                    ->route('runway.index', ['resource' => $customerResource->handle()])
                    ->can('view', $customerResource)
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
            $this->isOrExtendsClass(SimpleCommerce::productDriver()['repository'], \DuncanMcClean\SimpleCommerce\Products\EntryProductRepository::class)
        ) {
            Collection::computed(SimpleCommerce::productDriver()['collection'], 'raw_price', function ($entry, $value) {
                return $entry->get('price');
            });
        }

        if (
            $this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], \DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository::class)
        ) {
            Collection::computed(SimpleCommerce::orderDriver()['collection'], 'order_date', function ($entry, $value) {
                $order = Order::find($entry->id());

                if (! $order) {
                    return Carbon::now();
                }

                return $order->statusLog()->where('status', OrderStatus::Placed)->map->date()->last();
            });
        }
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
