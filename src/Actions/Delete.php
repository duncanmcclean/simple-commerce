<?php

namespace DuncanMcClean\SimpleCommerce\Actions;

use DuncanMcClean\SimpleCommerce\Coupons\Coupon;
use Statamic\Actions\Action;

class Delete extends Action
{
    protected $dangerous = true;

    protected static $handle = 'simple-commerce-delete';

    protected $icon = 'trash';

    public static function title()
    {
        return __('Delete');
    }

    public function visibleTo($item)
    {
        switch (true) {
            case $item instanceof Coupon:
                return true;
            default:
                return false;
        }
    }

    public function authorize($user, $item)
    {
        switch (true) {
            case $item instanceof Coupon:
                return $user->can('delete coupons');
            default:
                return false;
        }
    }

    public function buttonText()
    {
        /** @translation */
        return 'Delete|Delete :count items?';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to delete this?|Are you sure you want to delete these :count items?';
    }

    public function run($items, $values)
    {
        $items->each->delete();
    }
}
