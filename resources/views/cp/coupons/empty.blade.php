@extends('statamic::layout')
@section('title', __('Discounts'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="mt-8 py-8 text-center">
        <h1 class="flex items-center justify-center gap-2 text-[25px] font-medium antialiased">
            <ui-icon name="taxonomies" class="size-5 text-gray-500"></ui-icon>
            {{ __('Coupons') }}
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('Coupons are a great way to offer discounts to your customers. You can create coupons and apply them to orders.') }}">
        <ui-empty-state-item
            href="{{ cp_route('simple-commerce.coupons.create') }}"
            icon="taxonomies"
            heading="{{ __('Create a Coupon') }}"
            description="{{ __('Get started by creating your first coupon.') }}"
        ></ui-empty-state-item>
    </ui-empty-state-menu>

    <x-statamic::docs-callout :topic="__('Coupons')" url="https://simple-commerce.duncanmcclean.com/coupons" />
@stop