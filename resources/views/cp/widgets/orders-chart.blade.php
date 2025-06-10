<orders-chart-widget
    title="{{ __('Orders (Last 30 Days)') }}"
    icon='{!! DuncanMcClean\SimpleCommerce\SimpleCommerce::svg('shop') !!}'
    :data='@json($data)'
>
    <template #actions>
        <ui-button href="{{ $url }}">
            {{ __('View All') }}
        </ui-button>
    </template>
</orders-chart-widget>