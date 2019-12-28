@extends('statamic::layout')
@section('title', 'Edit Order')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <publish-form
            title="{{ $values['slug'] }}"
            action="{{ cp_route('orders.update', ['order' => $values['id']]) }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>
@endsection
