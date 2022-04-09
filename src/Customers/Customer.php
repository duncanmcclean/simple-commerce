<?php

namespace DoubleThreeDigital\SimpleCommerce\Customers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer as Contract;
use DoubleThreeDigital\SimpleCommerce\Data\HasData;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer as CustomerFacade;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Http\Resources\API\EntryResource;

class Customer implements Contract
{
    use HasData, Notifiable;

    public $id;
    public $email;
    public $data;

    public $resource;

    public function __construct()
    {
        $this->data = collect();
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function resource($resource = null)
    {
        return $this
            ->fluentlyGetOrSet('resource')
            ->args(func_get_args());
    }

    public function name(): ?string
    {
        return $this->get('name');
    }

    public function email($email = null)
    {
        return $this
            ->fluentlyGetOrSet('email')
            ->args(func_get_args());
    }

    public function orders(): Collection
    {
        return collect($this->has('orders') ? $this->get('orders') : [])
            ->map(function ($orderId) {
                return Order::find($orderId);
            });
    }

    public function addOrder($orderId): self
    {
        $orders = $this->has('orders') ? $this->get('orders') : [];
        $orders[] = $orderId;

        $this->set('orders', $orders);
        $this->save();

        return $this;
    }

    public function routeNotificationForMail($notification = null)
    {
        return $this->email();
    }

    public function beforeSaved()
    {
        return null;
    }

    public function afterSaved()
    {
        return null;
    }

    public function save(): self
    {
        if (method_exists($this, 'beforeSaved')) {
            $this->beforeSaved();
        }

        CustomerFacade::save($this);

        if (method_exists($this, 'afterSaved')) {
            $this->afterSaved();
        }

        return $this;
    }

    public function delete(): void
    {
        CustomerFacade::delete($this);
    }

    public function fresh(): self
    {
        $freshCustomer = CustomerFacade::find($this->id());

        $this->id = $freshCustomer->id;
        $this->email = $freshCustomer->email;
        $this->data = $freshCustomer->data;
        $this->resource = $freshCustomer->resource;

        return $this;
    }

    public function toArray(): array
    {
        $toArray = $this->data->toArray();

        $toArray['id'] = $this->id();

        return $toArray;
    }

    public function toResource()
    {
        return new EntryResource($this->resource());
    }

    public function toAugmentedArray(): array
    {
        if ($this->resource() instanceof Entry) {
            $blueprintFields = $this->resource()->blueprint()->fields()->items()->reject(function ($field) {
                return $field['handle'] === 'value';
            })->pluck('handle')->toArray();

            $augmentedData = $this->resource()->toAugmentedArray($blueprintFields);

            return array_merge(
                $this->toArray(),
                $augmentedData,
            );
        }

        if ($this->resource() instanceof Model) {
            $resource = \DoubleThreeDigital\Runway\Runway::findResourceByModel($this->resource());

            return $resource->augment($this->resource());
        }

        return null;
    }
}
