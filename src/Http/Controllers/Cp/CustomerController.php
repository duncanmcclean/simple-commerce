<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Facades\Customer;
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

        $customer = Customer::save($request->all());

        return array_merge($customer, [
            'redirect' => cp_route('customers.edit', ['customer' => $customer['id']])
        ]);
    }

    public function edit($customer)
    {
        $customer = Customer::find($customer);

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

        $customer = Customer::update($customer, $request->all());

        if ($request->slug != $customer) {
            return array_merge($customer, [
                'redirect' => cp_route('customers.edit', ['customer' => $customer['slug']])
            ]);
        }
    }

    public function destroy($customer)
    {
        $customer = Customer::delete($customer);

        return redirect(cp_route('customers.index'));
    }
}
