<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\CustomerStoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CustomerUpdateRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class CustomerController extends CpController
{
    public function index()
    {
        $this->authorize('view', Customer::class);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Simple Commerce'],
        ]);

        $customers = Customer::paginate(config('statamic.cp.pagination_size'));

        return view('commerce::cp.customers.index', [
            'crumbs' => $crumbs,
            'customers' => $customers,
            'createUrl' => (new Customer())->createUrl(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Customer::class);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Simple Commerce'],
            ['text' => 'Customers', 'url' => cp_route('customers.index')],
        ]);

        $blueprint = Blueprint::find('simple-commerce/customer');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.customers.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
        ]);
    }

    public function store(CustomerStoreRequest $request)
    {
        $this->authorize('create', Customer::class);

        $validated = $request->validated();

        $customer = new Customer();
        $customer->uuid = (new Stache())->generateId();
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->save();

        return ['redirect' => cp_route('customers.edit', ['customer' => $customer->uuid])];
    }

    public function edit($customer)
    {
        $customer = Customer::where('uuid', $customer)->first();

        $this->authorize('update', $customer);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Simple Commerce'],
            ['text' => 'Customers', 'url' => cp_route('customers.index')],
        ]);

        $blueprint = Blueprint::find('simple-commerce/customer');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.customers.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $customer->toArray(),
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
        ]);
    }

    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $validated = $request->validated();

        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->save();

        return $customer;
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        Order::where('customer_id', $customer->id)
            ->each(function ($order) {
                $order->delete();
            });

        Address::where('customer_id', $customer->id)
            ->each(function ($address) {
                $address->delete();
            });

        $customer->delete();

        return back()->with('success', 'Customer has been deleted.');
    }
}
