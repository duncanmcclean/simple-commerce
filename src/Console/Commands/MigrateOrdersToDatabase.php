<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Statamic\Console\RunsInPlease;
use Statamic\Contracts\Auth\User as AuthUser;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Collection;
use Statamic\Facades\User;

class MigrateOrdersToDatabase extends Command
{
    use ConfirmableTrait, RunsInPlease;

    protected $name = 'sc:migrate-to-database';

    protected $description = 'Migrates orders & customers to the database.';

    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        if (! isset(SimpleCommerce::orderDriver()['model'])) {
            return $this->error('You must run the `sc:switch-to-database` command before running this command.');
        }

        $ordersCollectionHandle = $this->ask('What is the handle of the orders collection?', 'orders');

        $previousCustomersDriver = $this->choice('Which customer driver do you wish to migrate from?', [
            'Entries',
            'Users',
        ]);

        if ($previousCustomersDriver === 'Entries') {
            $customersCollectionHandle = $this->ask('What is the handle of the customers collection?', 'customers');

            $this
                ->migrateEntryCustomers($customersCollectionHandle)
                ->migrateOrders($ordersCollectionHandle);
        }

        if ($previousCustomersDriver === 'Users') {
            $this
                ->migrateUserCustomers()
                ->migrateOrders($ordersCollectionHandle);
        }

        $this->info('Migration complete!');
    }

    protected function migrateEntryCustomers(string $collectionHandle): self
    {
        Collection::find($collectionHandle)
            ->queryEntries()
            ->get()
            ->reject(function (Entry $entry) {
                try {
                    Customer::findByEmail($entry->get('email'));

                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            })
            ->each(function (Entry $entry) {
                $data = $entry->data()->except(['email', 'orders']);
                $data['entry_id'] = $entry->id();

                $customer = Customer::make()
                    ->email($entry->get('email'))
                    ->data($data);

                $customer->save();
            });

        return $this;
    }

    protected function migrateUserCustomers(): self
    {
        User::all()
            ->reject(function (AuthUser $user) {
                try {
                    Customer::findByEmail($user->email());

                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            })
            ->each(function (AuthUser $user) {
                $data = $user->data()->except(['email', 'orders']);
                $data['user_id'] = $user->id();

                $customer = Customer::make()
                    ->email($user->email())
                    ->data($data);

                $customer->save();
            });

        return $this;
    }

    protected function migrateOrders(string $collectionHandle): self
    {
        Collection::find($collectionHandle)
            ->queryEntries()
            ->get()
            ->reject(function (Entry $entry) {
                $orderModelClass = SimpleCommerce::orderDriver()['model'];

                return (new $orderModelClass)
                    ->where('data->entry_id', $entry->id())
                    ->exists();
            })
            ->each(function (Entry $entry) {
                $data = $entry->data()->except([
                    'items', 'order_status', 'payment_status', 'grand_total', 'items_total', 'shipping_total', 'coupon_total', 'customer', 'coupon', 'gateway',
                ]);

                $data['entry_id'] = $entry->id();

                $order = Order::make()
                    ->status($entry->get('order_status') ?? OrderStatus::Cart)
                    ->paymentStatus($entry->get('payment_status') ?? PaymentStatus::Unpaid)
                    ->lineItems($entry->get('items', []))
                    ->grandTotal($entry->get('grand_total', 0))
                    ->itemsTotal($entry->get('items_total', 0))
                    ->taxTotal($entry->get('tax_total', 0))
                    ->shippingTotal($entry->get('shipping_total', 0))
                    ->couponTotal($entry->get('coupon_total', 0));

                if ($entry->get('customer')) {
                    $customerModelClass = SimpleCommerce::customerDriver()['model'];

                    $customer = (new $customerModelClass)
                        ->where('data->entry_id', $entry->get('customer'))
                        ->orWhere('data->user_id', $entry->get('customer'))
                        ->first();

                    if ($customer) {
                        $order->customer($customer->id);
                    }
                }

                if ($coupon = $entry->get('coupon')) {
                    $order->coupon($coupon);
                }

                if ($gateway = $entry->get('gateway')) {
                    $order->gatewayData(
                        gateway: $gateway['use'],
                        data: $gateway['data'],
                        refund: $gateway['refund'] ?? []
                    );
                }

                $order->data($data);

                $order->save();
            });

        return $this;
    }
}
