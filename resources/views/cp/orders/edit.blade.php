@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', $breadcrumbs->title($title))
@section('wrapper_class', 'max-w-3xl')

@section('content')

    <orders-publish-form
        publish-container="base"
        :initial-actions="{{ json_encode($actions) }}"
        method="patch"
        initial-title="{{ $title }}"
        initial-reference="{{ $order->reference() }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :initial-read-only="{{ json_encode($readOnly) }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        initial-listing-url="{{ cp_route('simple-commerce.orders.index',) }}"
        :initial-item-actions="{{ json_encode($itemActions) }}"
        item-action-url="{{ cp_route('simple-commerce.orders.actions.run') }}"
    ></orders-publish-form>

@endsection