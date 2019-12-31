<?php

namespace Damcclean\Commerce\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;
use Damcclean\Commerce\Models\Customer;

class CustomerFieldtype extends Relationship
{
    protected $categories = ['commerce'];
    protected $canCreate = false;
    protected $canEdit = false;
    protected $taggable = false;
    protected $icon = 'user';

    public function getIndexItems($request)
    {
        return $request->search
            ? $this->formatCustomers($this->searchCustomers($request->search))
            : $this->formatCustomers(Customer::all());
    }

    public function formatCustomers($customers)
    {
        return collect($customers)
            ->map(function ($customer) {
                return [
                    'id' => $customer['id'],
                    'title' => $customer['name'],
                    'email' => $customer['email'],
                ];
            });
    }

    protected function getColumns()
    {
        return [
            Column::make('name'),
            Column::make('email'),
        ];
    }

    public function searchCustomers($query)
    {
        return $results = Customer::all()
            ->filter(function ($item) use ($query) {
                return false !== stristr((string) $item['name'], $query);
            });
    }

    protected function toItemArray($id)
    {
        // TODO: Implement toItemArray() method.
    }

    public static function title()
    {
        return 'Customer';
    }
}
