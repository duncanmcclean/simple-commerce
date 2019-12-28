@extends('statamic::layout')
@section('title', 'Create Coupon')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <commerce-create-form inline-template>
        <publish-form
                title="Create Coupon"
                action="{{ cp_route('coupons.store') }}"
                :blueprint='@json($blueprint)'
                :meta='@json($meta)'
                :values='@json($values)'
                @saved="redirect"
        ></publish-form>
    </commerce-create-form>
@endsection
