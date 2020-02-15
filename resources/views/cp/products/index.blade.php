@extends('statamic::layout')
@section('title', 'Products')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="flex items-center mb-3">
        <h1 class="flex-1">
            Products
        </h1>

        <a class="btn btn-primary" href="{{ $createUrl }}">
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
                            {{ $product->variant_count }} 
                        </td>

                        <td>
                            @if($product->productCategory)
                                <a href="{{ $product->productCategory->showUrl() }}">{{ $product->productCategory->title }}</a>
                            @else
                                &mdash;
                            @endif
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

        @if($products->hasMorePages())
            <div class="w-full flex mt-3">
                <div class="flex-1"></div>

                <ul class="flex justify-center items-center list-reset">
                    @if($products->previousPageUrl())
                        <li class="mx-1">
                            <a href="{{ $products->previousPageUrl() }}"><span>&laquo;</span></a>
                        </li>
                    @endif

                    @foreach($products->links()->elements[0] as $number => $link)
                        <li class="mx-1 @if($number === $products->currentPage()) font-bold @endif">
                            <a href="{{ $link }}">{{ $number }}</a>
                        </li>
                    @endforeach

                    @if($products->nextPageUrl())
                        <li class="mx-1">
                            <a href="{{ $products->nextPageUrl() }}">
                                <span>»</span>
                            </a>
                        </li>
                    @endif
                </ul>

                <div class="flex flex-1">
                    <div class="flex-1"></div>
                </div>
            </div>
        @endif
    @else
        @include('statamic::partials.create-first', [
            'resource' => 'Product',
            'svg' => 'empty/collection',
            'route' => $createUrl
        ])
    @endif
@endsection
