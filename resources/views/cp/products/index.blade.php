@extends('statamic::layout')
@section('title', 'Products')

@section('content')
    <div class="flex mb-3">
        <h1 class="flex-1">Products</h1>

        <a href="{{ cp_route('products.create') }}" class="btn btn-primary">Create Product</a>
    </div>

    <commerce-listing
        model="products"
        cols='{{ json_encode([
            [
                'label' => 'Title',
                'field' => 'title',
            ],
            [
                'label' => 'Slug',
                'field' => 'slug'
            ],
            [
                'label' => 'Price',
                'field' => 'price'
            ]
        ]) }}'
        items='@json($products)'
    />
@endsection
