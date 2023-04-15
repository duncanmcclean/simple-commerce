@extends('statamic::layout')
@section('title', __('Coupons'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <div class="flex items-center justify-between mb-3">
        <h1 class="flex-1">{{ __('Coupons') }}</h1>

        @if(auth()->user()->can('create coupons'))
            <a class="btn-primary" href="{{ cp_route('simple-commerce.coupons.create') }}">{{ __('Create Coupon') }}</a>
        @endif
    </div>

    @if ($coupons->count())
        <div class="card p-0">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Discount') }}</th>
                        <th>{{ __('Redeemed') }}</th>
                        <th class="actions-column"></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($coupons as $coupon)
                        <tr id="coupon_{{ $coupon->id() }}">
                            <td>
                                <div class="flex items-center">
                                    <a class="font-mono" href="{{ $coupon->editUrl() }}">{{ $coupon->code() }}</a>
                                </div>
                            </td>
                            <td>
                                {{ $coupon->get('description') ?? '-' }}
                            </td>
                            <td>
                                {{ $coupon->discountText() }}
                            </td>
                            <td>
                                {{ $coupon->get('redeemed', 0) }} {{ __('times') }}
                            </td>
                            <td class="flex justify-end">
                                <dropdown-list class="mr-1">
                                    @if(auth()->user()->can('edit coupons'))
                                        <dropdown-item :text="__('Edit')" redirect="{{ $coupon->editUrl() }}"></dropdown-item>
                                    @endif

                                    @if(auth()->user()->can('delete coupons'))
                                        <dropdown-item :text="__('Delete')" class="warning" @click="$refs.deleter.confirm()">
                                            <resource-deleter
                                                ref="deleter"
                                                resource-title="{{ $coupon->get('code') }}"
                                                route="{{ $coupon->deleteUrl() }}"
                                                :reload="true"
                                                @deleted="document.getElementById('coupon_{{ $coupon->id() }}').remove()"
                                            ></resource-deleter>
                                        </dropdown-item>
                                    @endif
                                </dropdown-list>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="my-2">
            {{ $coupons->links('simple-commerce::cp.partials.pagination') }}
        </div>
    @else
        @include('statamic::partials.create-first', [
            'resource' => __('Coupon'),
            'svg' => 'empty/collection',
            'route' => cp_route('simple-commerce.coupons.create'),
        ])
    @endif
@endsection
