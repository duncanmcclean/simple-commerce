@php use Statamic\Statamic; @endphp

<top-customers-widget
    title="{{ __('Top Customers') }}"
    icon='{!! Statamic::svg('icons/users') !!}'
    :initial-items='@json($topCustomers)'
>
    <template #actions>
        <ui-button href="{{ $url }}">
            {{ __('View All') }}
        </ui-button>
    </template>
</top-customers-widget>
