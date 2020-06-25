<?php

namespace App;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\IsACustomer;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Statamic\Facades\User as StatamicUserFacade;

class User extends Authenticatable
{
    use Notifiable;
    use IsACustomer;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function isSuper()
    {
        return StatamicUserFacade::fromUser($this)->isSuper();
    }

    public function hasPermission($ability)
    {
        return StatamicUserFacade::fromUser($this)->hasPermission($ability);
    }

    public $fields = [
        'name', 'email',
    ];

    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
        ];
    }
}
