@extends('statamic::layout')
@section('title', 'Overview')

@section('content')
<header class="mb-3 flex justify-between items-center">
    <h1>{{ __('Overview') }}</h1>

    <!-- Toggle widgets (will tie in with user preferences) -->

    <div class="">
        <button class="btn flex items-center">
            Configure
            <svg viewBox="0 0 10 6.5" class="ml-1 w-2">
                <path
                  fill="currentColor"
                  d="M9.9 1.4 5 6.4l-5-5L1.4 0 5 3.5 8.5 0l1.4 1.4z"
                ></path>
            </svg>
        </button>
        <div class="popover-container dropdown-list">
          <div
            class="popover"
            style="
              position: absolute;
              inset: auto auto 0px 0px;
              margin: 0px;
              transform: translate(-153.333px, -47.8889px);
            "
            data-popper-reference-hidden=""
            data-popper-escaped=""
            data-popper-placement="top-end"
          >
            <div class="popover-content bg-white shadow-popover rounded-md">
              <li>
                <a href="https://example.com">Item 1 </a>
                <a href="https://example2.com">Item 2 </a>
              </li>
            </div>
          </div>
        </div>
      </div>
</header>

<!-- TODO: Warning about using entries driver with lots of entries -->

<div class="card p-2 content mb-2">
    <div class="flex flex-wrap -mx-2 mb-4">
        <div class="px-2 w-full">
            <p class="mb-4">Last month</p>
            <div class="px-1">
                @include('simple-commerce::cp.partials.line-chart', ['data' => $chartOrders])
            </div>
        </div>
        {{-- <div class="w-1/3 px-2">
            <p class="mb-4">Last week</p>
            <div class="px-1">
                @include('simple-commerce::cp.partials.line-chart', ['data' => $notFoundWeek])
            </div>
        </div>
        <div class="w-1/3 px-2">
            <p class="mb-4">Last day</p>
            <div class="px-1">
                @include('simple-commerce::cp.partials.line-chart', ['data' => $notFoundDay])
            </div>
        </div> --}}
    </div>
</div>

<div class="grid grid-cols-2 gap-2">
    <div class="flex-1 card p-0 overflow-hidden h-full">
        <div class="flex justify-between items-center p-2">
            <h2>
                <span>{{ __('Recent Orders') }}</span>
            </h2>
        </div>

        <ul class="px-2">
            @foreach($recentOrders as $order)
                <li class="py-1 flex items-center justify-between">
                    <a href="#"><strong>#{{ $order->orderNumber() }}</strong> - {{ \DoubleThreeDigital\SimpleCommerce\Currency::parse($order->grandTotal(), \Statamic\Facades\Site::current()) }}</a>
                    <span class="text-sm">{{ $order->get('paid_date') }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="flex-1 card p-0 overflow-hidden h-full">
        <div class="flex justify-between items-center p-2">
            <h2>
                <span>{{ __('Top Customers') }}</span>
            </h2>
        </div>

        <ul class="px-2">
            @foreach($topCustomers as $customer)
                <li class="py-1 flex items-center justify-between">
                    <a href="#">{{ $customer->email() }}</a>
                    <span class="text-sm">{{ $customer->orders()->count() }} orders</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="flex-1 card p-0 overflow-hidden h-full">
        <div class="flex justify-between items-center p-2">
            <h2>
                <span>{{ __('Low Stock Products') }}</span>
            </h2>
        </div>

        <ul class="px-2">
            @foreach($lowStockProducts as $product)
                <li class="py-1 flex items-center justify-between">
                    <a href="#">{{ $product->get('title') }}</a>
                    <span class="text-sm @if($product->stock() == 0) text-red-light @endif">{{ $product->stock() }} remaining</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>

@include('statamic::partials.docs-callout', [
    'topic' => 'Simple Commerce',
    'url' => 'https://simple-commerce.duncanmcclean.com/?ref=cp_overview'
])
@endsection
