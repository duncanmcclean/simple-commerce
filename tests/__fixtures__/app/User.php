<?php

namespace App;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\IsACustomer;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Statamic\Facades\User as StatamicUserFacade;

class User extends Authenticatable
{
    use Notifiable, IsACustomer;

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
        'name', 'email', 'password',
    ];

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];
    }
}
