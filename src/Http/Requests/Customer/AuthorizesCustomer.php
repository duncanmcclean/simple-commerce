<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\Customer;

use DuncanMcClean\SimpleCommerce\Customers\Concerns\AuthorizesCustomerAccess;
use DuncanMcClean\SimpleCommerce\Facades\Customer;

trait AuthorizesCustomer
{
    use AuthorizesCustomerAccess;

    public function authorize(): bool
    {
        return $this->belongsToCurrentUser(Customer::find($this->route('customer')));
    }
}
