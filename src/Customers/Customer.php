<?php

namespace DoubleThreeDigital\SimpleCommerce\Customers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer as Contract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\IsEntry;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;

class Customer implements Contract
{
    use IsEntry, HasData;

    public $id;
    public $site;
    public $title;
    public $slug;
    public $data;
    public $published;

    protected $entry;
    protected $collection;

    public function findByEmail(string $email): self
    {
        $entry = Entry::query()
            ->where('collection', config('simple-commerce.collections.customers'))
            ->where('slug', Str::slug($email))
            ->first();

        if (! $entry) {
            throw new CustomerNotFound(__('simple-commerce::customers.customer_not_found_by_email', ['email' => $email]));
        }

        return $this->find($entry->id());
    }

    public function name(): string
    {
        return $this->get('name');
    }

    public function email(): string
    {
        return $this->get('email');
    }

    public function generateTitleAndSlug(): self
    {
        $name = '';
        $email = '';

        if ($this->has('name')) {
            $name = $this->get('name');
        }

        if ($this->has('email')) {
            $email = $this->get('email');
        }

        $this->title = __('simple-commerce::customers.customer_entry_title', [
            'name' => $name,
            'email' => $email,
        ]);

        dump($this->title, $this->data);

        $this->slug = Str::slug($email);

        return $this;
    }

    public function beforeSaved()
    {
        if (is_null($this->title) || is_null($this->slug) || $this->slug === '') {
            $this->generateTitleAndSlug();

            $this->set('title', $this->title);
        }
    }

    public function collection(): string
    {
        return config('simple-commerce.collections.customers');
    }

    public static function bindings(): array
    {
        return [];
    }
}
