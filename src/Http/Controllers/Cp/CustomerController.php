<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CustomerStoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CustomerUpdateRequest;
use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class CustomerController extends CpController
{
    public function index()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
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
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Customers', 'url' => cp_route('customers.index')],
        ]);

        $blueprint = Blueprint::find('customer');

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
        $customer->uid = (new Stache())->generateId();
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->save();

        if ($request->billing_address_1) {
            $billingAddress = new Address();
            $billingAddress->uid = (new Stache())->generateId();
            $billingAddress->country_id = $request->billing_country[0];
            $billingAddress->state_id = $request->billing_state ?? null;
            $billingAddress->name = $request->name;
            $billingAddress->address1 = $request->billing_address_1;
            $billingAddress->address2 = $request->billing_address_2;
            $billingAddress->address3 = $request->billing_address_3;
            $billingAddress->city = $request->billing_city;
            $billingAddress->zip_code = $request->billing_zip_code;
            $billingAddress->customer_id = $customer->id;
            $billingAddress->save();

            $customer->default_billing_address_id = $billingAddress->id;
            $customer->save();
        }

        if ($request->shipping_address_1) {
            $shippingAddress = new Address();
            $shippingAddress->uid = (new Stache())->generateId();
            $shippingAddress->country_id = $request->shipping_country[0];
            $shippingAddress->state_id = $request->shipping_state ?? null;
            $shippingAddress->name = $request->name;
            $shippingAddress->address1 = $request->shipping_address_1;
            $shippingAddress->address2 = $request->shipping_address_2;
            $shippingAddress->address3 = $request->shipping_address_3;
            $shippingAddress->city = $request->shipping_city;
            $shippingAddress->zip_code = $request->shipping_zip_code;
            $shippingAddress->customer_id = $customer->id;
            $shippingAddress->save();

            $customer->default_shipping_address_id = $shippingAddress->id;
            $customer->save();
        }

        return ['redirect' => cp_route('customers.edit', ['customer' => $customer->uid])];
    }

    public function edit($customer)
    {
        $customer = Customer::where('uid', $customer)
            ->with('billingAddress')
            ->with('shippingAddress')
            ->first();

        $this->authorize('update', $customer);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Customers', 'url' => cp_route('customers.index')],
        ]);

//        dd(collect($customer)->toArray());

        $blueprint = Blueprint::find('customer');

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

        $billingAddress = Address::find($customer->defaultBillingAddress->id);
        $billingAddress->country_id = $request->billing_country[0];
        $billingAddress->state_id = $request->billing_state ?? null;
        $billingAddress->name = $request->name;
        $billingAddress->address1 = $request->billing_address_1;
        $billingAddress->address2 = $request->billing_address_2;
        $billingAddress->address3 = $request->billing_address_3;
        $billingAddress->city = $request->billing_city;
        $billingAddress->zip_code = $request->billing_zip_code;
        $billingAddress->customer_id = $customer->id;
        $billingAddress->save();

        $shippingAddress = Address::find($customer->defaultShippingAddress->id);
        $shippingAddress->country_id = $request->shipping_country[0];
        $shippingAddress->state_id = $request->shipping_state ?? null;
        $shippingAddress->name = $request->name;
        $shippingAddress->address1 = $request->shipping_address_1;
        $shippingAddress->address2 = $request->shipping_address_2;
        $shippingAddress->address3 = $request->shipping_address_3;
        $shippingAddress->city = $request->shipping_city;
        $shippingAddress->zip_code = $request->shipping_zip_code;
        $shippingAddress->customer_id = $customer->id;
        $shippingAddress->save();

        return $customer;
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        $customer->delete(); // TODO: what will happen with their orders? we might want to delete them

        return redirect(cp_route('customers.index'));
    }
}
