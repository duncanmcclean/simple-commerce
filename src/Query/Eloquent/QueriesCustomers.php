<?php

namespace DuncanMcClean\SimpleCommerce\Query\Eloquent;

use Illuminate\Support\Str;

trait QueriesCustomers
{
    /**
     * The "customer" column contains both user IDs and JSON objects for guest customers. In order
     * to query guest customers, we need to handle the query differently depending on the value.
     *
     * We don't need to do this for flat file orders because the Stache uses the getQueryableValue() method.
     */
    private function queryByCustomer($operator = null, $value = null, $boolean = 'and'): self
    {
        if (Str::startsWith($value ?? $operator, 'guest::')) {
            $email = Str::after($value ?? $operator, 'guest::');

            $this->builder->whereRaw("customer LIKE '{%' AND json_extract(customer, '$.email') = ?", [$email]);

            return $this;
        }

        return parent::where('customer', $operator, $value, $boolean);
    }
}