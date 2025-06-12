@php use DuncanMcClean\SimpleCommerce\SimpleCommerce; @endphp

<recent-orders-widget
    title="{{ __('Recent Orders') }}"
    icon='{!! SimpleCommerce::svg('shop') !!}'
    :initial-items='@json($recentOrders)'
>
    <template #actions>
        <ui-button href="{{ $url }}">
            {{ __('View All') }}
        </ui-button>
    </template>
</recent-orders-widget>
