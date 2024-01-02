<div class="card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-4">
        <h2>
            <a class="flex items-center" href="{{ $url }}">
                <span>{{ __('Orders (Last 30 Days)') }}</span>
            </a>
        </h2>
    </div>
    <orders-chart :data='@json($data)' />
</div>
