@extends('statamic::layout')
@section('title', 'Product Categories')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="flex mb-3">
        <h1 class="flex-1">Product Categories</h1>

        <a href="{{ cp_route('product-categories.create') }}" class="btn btn-primary">Create Category</a>
    </div>

    <commerce-listing
            model="product-categories"
            cols='{{ json_encode([
            [
                'label' => 'Title',
                'field' => 'title',
            ],
            [
                'label' => 'Slug',
                'field' => 'slug'
            ],
        ]) }}'
            items='@json($categories)'
            primary='title'
    />
@endsection
