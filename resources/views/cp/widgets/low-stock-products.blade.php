<div class="card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-4">
        <h2>
            <a class="flex items-center" href="{{ $url }}">
                <div class="h-6 w-6 mr-2 text-gray-800 dark:text-dark-100">
                    @cp_svg('icons/light/entries')
                </div>
                <span>{{ __('Low Stock Products') }}</span>
            </a>
        </h2>
    </div>
    <div>
        @if($lowStockProducts->isEmpty())
            <p class="p-4 pt-2 text-sm text-gray-600">There are no low stock products.</p>
        @else
            <table data-size="sm" tabindex="0" class="data-table">
                <tbody tabindex="0">
                    @foreach ($lowStockProducts as $lowStockProduct)
                        <tr class="sortable-row outline-none" tabindex="0">
                            <td>
                                <a href="{{ $lowStockProduct['edit_url'] }}">
                                    {{ $lowStockProduct['title'] }}
                                </a>
                            </td>

                            <td align="right">
                                <span class="text-xs @if($lowStockProduct['stock'] < 1) text-red-light @else text-gray-600 @endif">
                                    {{ $lowStockProduct['stock'] }} remaining
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
