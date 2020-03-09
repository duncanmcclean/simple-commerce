@extends('statamic::layout')
@section('title', 'Edit Product Category')
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <publish-form
            title="{{ $values->title }}"
            action="{{ cp_route('product-categories.update', ['category' => $values['uuid']]) }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>
@endsection
