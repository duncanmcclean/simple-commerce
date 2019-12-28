@extends('statamic::layout')
@section('title', 'Create Customer')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <commerce-create-form inline-template>
        <publish-form
                title="Create Customer"
                action="{{ cp_route('customers.store') }}"
                :blueprint='@json($blueprint)'
                :meta='@json($meta)'
                :values='@json($values)'
                @saved="redirect"
        ></publish-form>
    </commerce-create-form>
@endsection
