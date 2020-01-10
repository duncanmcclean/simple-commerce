@extends('statamic::layout')
@section('title', "Products in $category->title")

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="flex items-center mb-3">
        <h1 class="flex-1">
            {{ $category->title }}
        </h1>

        <dropdown-list>
            <dropdown-item text="Edit Category" redirect="{{ $category->editUrl() }}"></dropdown-item>
        </dropdown-list>

        <a class="btn btn-primary ml-2" href="{{ $createUrl }}">
            Create Product
        </a>
    </div>

    @if ($products->count())
        <table class="bg-white data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Variants</th>
                    <th>Category</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="little-dot mr-1 @if($product->is_enabled) bg-green @else bg-gray-40 @endif"></div>
                                <a href="{{ $product->editUrl() }}">{{ $product->title }}</a>
                            </div>
                        </td>

                        <td>
                            {{ $product->variants->count() }} variants
                        </td>

                        <td>
                            <a href="{{ $product->productCategory->showUrl() }}">{{ $product->productCategory->title }}</a>
                        </td>

                        <td class="flex justify-end">
                            <dropdown-list>
                                <dropdown-item text="Edit" redirect="{{ $product->editUrl() }}"></dropdown-item>
                                <dropdown-item class="warning" text="Delete" redirect="{{ $product->deleteUrl() }}"></dropdown-item>
                            </dropdown-list>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        @component('statamic::partials.create-first', [
            'resource' => 'Product',
            'svg' => 'empty/collection',
        ])
            <a
                    class="btn btn-primary"
                    href="{{ $createUrl }}"
            >
                Create Product
            </a>
        @endcomponent
    @endif
@endsection
