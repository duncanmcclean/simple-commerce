<?php

namespace DoubleThreeDigital\SimpleCommerce\Customers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer as Contract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Facades\Stache;
use Statamic\Facades\User;
use Statamic\Http\Resources\API\UserResource;

class UserCustomer implements Contract
{
    use HasData;
    use Notifiable;

    public $id;
    public $name;
    public $email;
    public $data;

    protected $user;

    public function all()
    {
        return User::all();
    }

    public function query()
    {
        return collect(User::all());
    }

    public function find($id): self
    {
        $this->user = User::find((string) $id);

        if (!$this->user) {
            throw new CustomerNotFound("Customer with ID [{$id}] could not be found.");
        }

        $this->id = $this->user->id();
        $this->name = $this->user->get('name');
        $this->email = $this->user->email();
        $this->data = $this->user->data();

        return $this;
    }

    public function findByEmail(string $email): self
    {
        $user = User::findByEmail($email);

        if (!$user) {
            throw new CustomerNotFound("Customer with email [{$email}] could not be found.");
        }

        return $this->find($user->id());
    }

    public function create(array $data = [], string $site = ''): self
    {
        $this->id = Stache::generateId();
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->email = $data['email']; // TODO: if it doesn't exist, throw an exception

        $data = array_merge($data, $this->defaultFieldsInBlueprint());

        $this->data(
            Arr::except($data, ['id', 'name', 'email'])
        );

        $this->save();

        return $this;
    }

    public function save(): self
    {
        if (!$this->user) {
            $this->user = User::make()
                ->id($this->id);
        }

        $data = $this->data;

        if ($this->name) {
            $data['name'] = $this->name;
        }

        $this->user
            ->email($this->email)
            ->data($data);

        if (method_exists($this, 'beforeSaved')) {
            $this->beforeSaved();
        }

        $this->user->save();

        if (method_exists($this, 'afterSaved')) {
            $this->afterSaved();
        }

        return $this;
    }

    public function delete()
    {
        $this->user->delete();
    }

    public function user()
    {
        return $this->user;
    }

    public function toResource()
    {
        return new UserResource($this->user);
    }

    public function toAugmentedArray($keys = null)
    {
        return $this->user->toAugmentedArray($keys);
    }

    public function id()
    {
        return $this->id;
    }

    public function title(string $title = null)
    {
        if ($title === null) {
            return "$this->name <$this->email>";
        }

        // Just do nothing.
        return $this;
    }

    public function slug(string $slug = null)
    {
        if ($slug === null) {
            return $this->user->id();
        }

        // Just do nothing.
        return $this;
    }

    public function site($site = null)
    {
        if ($site === null) {
            return null;
        }

        // Just do nothing.
        return $this;
    }

    public function fresh(): self
    {
        return $this->find($this->id);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
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

        return $this;
    }

    public function routeNotificationForMail($notification = null)
    {
        return $this->email();
    }

    protected function defaultFieldsInBlueprint(): array
    {
        return User::blueprint()->fields()->items()
            ->where('field.default', '!==', null)
            ->mapWithKeys(function ($field) {
                return [$field['handle'] => $field['field']['default']];
            })
            ->toArray();
    }

    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;
        $this->user()->set($key, $value)->save();

        return $this;
    }

    public function beforeSaved()
    {
        return null;
    }

    public function afterSaved()
    {
        return null;
    }

    public static function bindings(): array
    {
        return [];
    }
}
