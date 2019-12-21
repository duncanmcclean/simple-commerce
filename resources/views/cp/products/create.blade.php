@extends('statamic::layout')
@section('title', 'Create Product')

@section('content')
    <commerce-create-form inline-template>
        <publish-form
                title="Create Product"
                action="{{ cp_route('products.store') }}"
                :blueprint='@json($blueprint)'
                :meta='@json($meta)'
                :values='@json($values)'
                @saved="redirect"
        ></publish-form>
    </commerce-create-form>
@endsection
