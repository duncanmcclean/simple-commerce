<div class="card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-4">
        <h2>
            <a class="flex items-center" href="{{ $url }}">
                <div class="h-6 w-6 mr-2 text-gray-800 dark:text-dark-100">
                    @cp_svg('icons/light/user')
                </div>
                <span>{{ __('Top Customers') }}</span>
            </a>
        </h2>
    </div>
    <div>
        @if($topCustomers->isEmpty())
            <p class="p-4 pt-2 text-sm text-gray-600">There are no top customers.</p>
        @else
            <table data-size="sm" tabindex="0" class="data-table">
                <tbody tabindex="0">
                    @foreach ($topCustomers as $topCustomer)
                        <tr class="sortable-row outline-none" tabindex="0">
                            <td>
                                <a href="{{ $topCustomer['edit_url'] }}">
                                    {{ $topCustomer['email'] }}
                                </a>
                            </td>

                            <td align="right">
                                <span class="text-xs text-gray-600">{{ $topCustomer['orders_count'] }} {{ Str::of('order')->plural($topCustomer['orders_count']) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
