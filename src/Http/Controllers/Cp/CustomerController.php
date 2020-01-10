<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Models\Address;
use Damcclean\Commerce\Models\Customer;
use Damcclean\Commerce\Http\Requests\CustomerStoreRequest;
use Damcclean\Commerce\Http\Requests\CustomerUpdateRequest;
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

        $customers = Customer::all()
            ->map(function ($customer) {
                return array_merge($customer->toArray(), [
                    'title' => $customer->name,
                    'edit_url' => cp_route('customers.edit', ['customer' => $customer->uid]),
                    'delete_url' => cp_route('customers.destroy', ['customer' => $customer->uid]),
                ]);
            });

        return view('commerce::cp.customers.index', [
            'customers' => $customers,
            'crumbs' => $crumbs,
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

        $customer->default_billing_address_id = $billingAddress->id;
        $customer->default_shipping_address_id = $shippingAddress->id;
        $customer->save();

        return ['redirect' => cp_route('customers.edit', ['customer' => $customer->uid])];
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Customers', 'url' => cp_route('customers.index')],
        ]);

        $customer = Customer::find($customer)->first();

        $billingAddress = $customer->defaultBillingAddress;
        $shippingAddress = $customer->defaultShippingAddress;

        $customer = array_merge($customer->toArray(), [
            'billing_address_1' => $billingAddress->address1,
            'billing_address_2' => $billingAddress->address2,
            'billing_address_3' => $billingAddress->address3,
            'billing_city' => $billingAddress->city,
            'billing_zip_code' => $billingAddress->zip_code,
            'billing_country' => $billingAddress->country,
            'billing_state' => $billingAddress->state_id,

            'shipping_address_1' => $shippingAddress->address1,
            'shipping_address_2' => $shippingAddress->address2,
            'shipping_address_3' => $shippingAddress->address3,
            'shipping_city' => $shippingAddress->city,
            'shipping_zip_code' => $shippingAddress->zip_code,
            'shipping_country' => $shippingAddress->country,
            'shipping_state' => $shippingAddress->state_id,
        ]);

        $blueprint = Blueprint::find('customer');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.customers.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $customer,
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

        $customer->delete();

        return redirect(cp_route('customers.index'));
    }
}
