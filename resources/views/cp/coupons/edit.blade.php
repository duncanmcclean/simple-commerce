@extends('statamic::layout')
@section('title', $breadcrumbs->title($title))
@section('wrapper_class', 'max-w-3xl')

@section('content')

    <coupon-publish-form
        publish-container="base"
         :initial-actions="{{ json_encode($actions) }}"
        method="patch"
        initial-title="{{ $title }}"
        initial-reference="{{ $coupon->reference() }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :initial-read-only="{{ json_encode($readOnly) }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        initial-listing-url="{{ cp_route('simple-commerce.coupons.index',) }}"
        :initial-item-actions="{{ json_encode($itemActions) }}"
        item-action-url="{{ cp_route('simple-commerce.coupons.actions.run') }}"
    ></coupon-publish-form>

@endsection
