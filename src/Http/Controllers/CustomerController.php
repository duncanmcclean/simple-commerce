<?php

namespace Damcclean\Commerce\Http\Controllers;

use Facades\Damcclean\Commerce\Models\Coupon;
use Facades\Damcclean\Commerce\Models\Customer;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class CustomerController extends CpController
{
    public function index()
    {
        return view('commerce::cp.customers.index', [
            'customers' => Customer::all()
        ]);
    }

    public function create()
    {
        $blueprint = Blueprint::find('customer');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.customers.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = []; // WIP

        $slug = $request->stripe_customer_id;

        $customer = Customer::save($slug, $request->all());

        return array_merge($customer->toArray(), [
            'redirect' => cp_route('customers.edit', ['customer' => $slug])
        ]);
    }

    public function edit($customer)
    {
        $customer = Customer::get($customer);

        $blueprint = Blueprint::find('customer');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.customers.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $customer,
            'meta'      => $fields->meta(),
        ]);
    }

    public function update(Request $request, $customer)
    {
        $validated = []; // wip

        $customer = Coupon::update($customer, $request->all());

        if ($request->slug != $customer) {
            return array_merge($customer->toArray(), [
                'redirect' => cp_route('customers.edit', ['customer' => $customer->slug])
            ]);
        }
    }

    public function destroy($customer)
    {
        $customer = Coupon::delete($customer);

        return redirect(cp_route('customers.index'));
    }
}
