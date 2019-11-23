@extends('statamic::layout')
@section('title', 'Create Product')

@section('content')
    <publish-form
            title="Create Product"
            action="{{ cp_route('products.store') }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>
@endsection
