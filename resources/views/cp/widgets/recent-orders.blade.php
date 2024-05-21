<div class="card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-4">
        <h2>
            <a class="flex items-center" href="{{ $url }}">
                <div class="h-6 w-6 mr-2 text-gray-800 dark:text-dark-100">
                    {!! \DuncanMcClean\SimpleCommerce\SimpleCommerce::svg('shop') !!}
                </div>
                <span>{{ __('Recent Orders') }}</span>
            </a>
        </h2>
    </div>
    <div>
        @if($recentOrders->isEmpty())
            <p class="p-4 pt-2 text-sm text-gray-600">There are no recent orders.</p>
        @else
            <table data-size="sm" tabindex="0" class="data-table">
                <tbody tabindex="0">
                    @foreach ($recentOrders as $recentOrder)
                        <tr class="sortable-row outline-none" tabindex="0">
                            <td>
                                <a href="{{ $recentOrder['edit_url'] }}">
                                    <strong class="font-medium">#{{ $recentOrder['order_number'] }}</strong>
                                    - {{ $recentOrder['grand_total'] }}
                                </a>
                            </td>

                            <td align="right">
                                <span class="text-xs text-gray-600">{{ $recentOrder['date'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
