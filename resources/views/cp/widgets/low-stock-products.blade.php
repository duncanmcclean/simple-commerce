@php use Statamic\Statamic; @endphp

<low-stock-products-widget
        title="{{ __('Low Stock Products') }}"
        icon='{!! Statamic::svg('icons/collections') !!}'
        :initial-items='@json($lowStockProducts)'
>
    <template #actions>
        <ui-button href="{{ $listingUrl }}">
            {{ __('View All') }}
        </ui-button>
    </template>
</low-stock-products-widget>