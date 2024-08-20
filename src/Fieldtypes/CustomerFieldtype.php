<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;

class CustomerFieldtype extends Fieldtype
{
    public function preload()
    {
        $userField = new Field('user', [
            'type' => 'users',
            'max_items' => 1,
        ]);

        return [
            'user' => $userField->meta(),
            'canCreateUsers' => User::current()->isSuper() || User::current()->hasPermission('create users'),
            'convertGuestToUserUrl' => cp_route('simple-commerce.convert-guest-to-user'),
        ];
    }

    public function preProcess($data)
    {
        if (! is_object($data)) {
            return [
                'id' => $data,
                'invalid' => true,
            ];
        }

        if ($data instanceof GuestCustomer) {
            return [
                'type' => 'guest',
                'id' => $data->id(),
                'reference' => $data->id(),
                'name' => $data->name(),
                'email' => $data->email(),
                'viewable' => true,
                'editable' => false,
            ];
        }

        return [
            'type' => 'user',
            'id' => $data->id(),
            'reference' => $data->reference(),
            'name' => $data->name(),
            'email' => $data->email(),
            'viewable' => User::current()->can('view', $data),
            'editable' => User::current()->can('view', $data),
            'edit_url' => $data->editUrl(),
        ];
    }
}