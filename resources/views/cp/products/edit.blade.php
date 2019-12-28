@extends('statamic::layout')
@section('title', 'Edit Product')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <publish-form
            title="{{ $values['title'] }}"
            action="{{ cp_route('products.update', ['product' => $values['id']]) }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>
@endsection
