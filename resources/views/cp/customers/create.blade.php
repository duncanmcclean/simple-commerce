@extends('statamic::layout')
@section('title', 'Create Customer')
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <commerce-create-form inline-template>
        <publish-form
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
            title="Create Customer"
            name="customer-publish-form"
            :breadcrumbs='@json($crumbs)'
            action="{{ $action }}"
            method="post"
            @saved="redirect"
        ></publish-form>
    </commerce-create-form>
@endsection
