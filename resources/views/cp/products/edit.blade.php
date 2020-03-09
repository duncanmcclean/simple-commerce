@extends('statamic::layout')
@section('title', 'Edit Product')
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <publish-form
        title="{{ $values['title'] }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
            action="{{ $action }}"
            @saved="window.location.reload()"
    ></publish-form>
@endsection
