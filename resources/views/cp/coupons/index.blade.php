@extends('statamic::layout')
@section('title', 'Coupons')

@section('content')
    <div class="flex items-center justify-between mb-3">
        <h1 class="flex-1">Coupons</h1>
        <a class="btn-primary" href="{{ $createUrl }}">Create Coupon</a>
    </div>

    @if ($coupons->count())
        <div class="card p-0">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Coupon Code</th>
                        <th>Affect</th>
                        <th>Uses</th>
                        <th class="actions-column"></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($coupons as $coupon)
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div class="little-dot mr-1 @if($coupon->isActive()) bg-green @else bg-gray-40 @endif"></div>
                                    <a href="{{ $coupon->editUrl() }}">{{ $coupon->name }}</a>
                                </div>
                            </td>
                            <td>{{ $coupon->code }}</td>
                            <td>{{ $coupon->affect }}</td>
                            <td>{{ $coupon->uses() }}</td>
                            <td class="flex justify-end">
                                <simple-commerce-actions>
                                    <simple-commerce-action-item
                                        type="standard"
                                        text="Edit"
                                        action="{{ $coupon->editUrl() }}"
                                    ></simple-commerce-action-item>
                                    <simple-commerce-action-item
                                        type="delete"
                                        text="Delete"
                                        action="{{ $coupon->deleteUrl() }}"
                                        method="delete"
                                        modal-title="Delete Coupon"
                                        modal-text="Are you sure you want to delete this coupon?"
                                    ></simple-commerce-action-item>
                                </simple-commerce-actions>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($coupons->hasMorePages())
                <div class="w-full flex mt-3">
                    <div class="flex-1"></div>

                    <ul class="flex justify-center items-center list-reset">
                        @if($coupons->previousPageUrl())
                            <li class="mx-1">
                                <a href="{{ $coupons->previousPageUrl() }}"><span>&laquo;</span></a>
                            </li>
                        @endif

                        @foreach($coupons->links()->elements[0] as $number => $link)
                            <li class="mx-1 @if($number === $coupons->currentPage()) font-bold @endif">
                                <a href="{{ $link }}">{{ $number }}</a>
                            </li>
                        @endforeach

                        @if($coupons->nextPageUrl())
                            <li class="mx-1">
                                <a href="{{ $coupons->nextPageUrl() }}">
                                    <span>»</span>
                                </a>
                            </li>
                        @endif
                    </ul>

                    <div class="flex flex-1">
                        <div class="flex-1"></div>
                    </div>
                </div>
            @endif
        </div>
    @else
        @include('statamic::partials.create-first', [
            'resource' => 'Coupon',
            'svg' => 'empty/collection',
            'route' => $createUrl
        ])
    @endif
@endsection
