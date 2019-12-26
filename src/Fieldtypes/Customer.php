<?php

namespace Damcclean\Commerce\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;
use Damcclean\Commerce\Facades\Customer as CustomerFacade;

class Customer extends Relationship
{
    protected $canCreate = false;
    protected $canEdit = false;
    protected $taggable = false;

    protected function toItemArray($id)
    {
        dd($id);
    }

    public function getIndexItems($request)
    {
        return $request->search
            ? $this->formatCustomers($this->searchCustomers($request->search))
            : $this->formatCustomers(CustomerFacade::all());
    }

//    public function getItemData($values)
//    {
//        dd($values);
//    }

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
            Column::make('title'),
            Column::make('email'),
        ];
    }

    public function searchCustomers($query)
    {
        return $results = CustomerFacade::all()
            ->filter(function ($item) use ($query) {
                return false !== stristr((string) $item['name'], $query);
            });
    }
}
