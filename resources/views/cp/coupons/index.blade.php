@extends('statamic::layout')
@section('title', 'Coupons')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="flex mb-3">
        <h1 class="flex-1">Coupons</h1>

        <a href="{{ cp_route('coupons.create') }}" class="btn btn-primary">Create Coupon</a>
    </div>

    <commerce-listing
        model="coupons"
        cols='{{ json_encode([
            [
                'label' => 'Title',
                'field' => 'title',
            ],
            [
                'label' => 'Description',
                'field' => 'description'
            ]
        ]) }}'
        items='@json($coupons)'
        primary="title"
    />
@endsection
