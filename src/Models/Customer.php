<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Events\CustomerCreated;
use DoubleThreeDigital\SimpleCommerce\Events\CustomerUpdated;
use DoubleThreeDigital\SimpleCommerce\IsACustomer;
use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Statamic\Facades\Blueprint;

class Customer extends Model
{
    /**
     * This model is pretty much useless right now...
     */

    use Notifiable, HasUuid, IsACustomer;

    protected $fillable = [
        'uuid', 'name', 'email',
    ];

    public function createUrl()
    {
        return cp_route('customers.create');
    }

    public function editUrl()
    {
        return cp_route('customers.edit', ['customer' => $this->uuid]);
    }

    public function updateUrl()
    {
        return cp_route('customers.update', ['customer' => $this->uuid]);
    }

    public function deleteUrl()
    {
        return cp_route('customers.destroy', ['customer' => $this->uuid]);
    }

    public function blueprint()
    {
        return Blueprint::find('customer');
    }
}
