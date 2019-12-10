@extends('statamic::layout')
@section('title', 'Edit Product')

@section('content')
    <publish-form
            title="{{ $values['title'] }}"
            action="{{ cp_route('products.update', ['product' => $values['id']]) }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>
@endsection
