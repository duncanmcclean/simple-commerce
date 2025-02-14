<?php

namespace DuncanMcClean\SimpleCommerce\Scopes\Filters;

use Statamic\Facades\Site;
use Statamic\Query\Scopes\Filter;

class OrderSite extends Filter
{
    public $pinned = true;

    public static function title()
    {
        return __('Site');
    }

    public function fieldItems()
    {
        return [
            'site' => [
                'display' => __('Site'),
                'type' => 'radio',
                'options' => $this->options()->all(),
            ],
        ];
    }

    public function autoApply()
    {
        return [
            'site' => Site::selected()->handle(),
        ];
    }

    public function apply($query, $values)
    {
        $query->where('site', $values['site']);
    }

    public function badge($values)
    {
        $site = Site::get($values['site']);

        return __('Site').': '.__($site->name());
    }

    public function visibleTo($key)
    {
        return Site::hasMultiple() && $key === 'orders';
    }

    protected function options()
    {
        return Site::all()->mapWithKeys(fn ($site) => [$site->handle() => __($site->name())]);
    }
}
