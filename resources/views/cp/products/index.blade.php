@extends('statamic::layout')
@section('title', 'Products')

@section('content')
    <div class="flex items-center justify-between mb-3">
        <h1 class="flex-1">Products</h1>

        <dropdown-list class="mr-1">
            @can('view', \DoubleThreeDigital\SimpleCommerce\Models\ProductCategory::class)
                <dropdown-item text="Configure Categories" redirect="{{ cp_route('product-categories.index') }}"></dropdown-item>
            @endcan
        </dropdown-list>

        <a class="btn-primary" href="{{ $createUrl }}">Create Product</a>
    </div>

    @if ($products->count())
        <div class="card p-0">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Variants</th>
                        <th>Category</th>
                        <th class="actions-column"></th>
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

                            <td>{{ $product->variant_count }}</td>

                            <td>
                                @if($product->productCategory)
                                    <a href="{{ $product->productCategory->showUrl() }}">{{ $product->productCategory->title }}</a>
                                    @else
                                    &mdash;
                                @endif
                            </td>

                            <td class="flex justify-end">
                                <simple-commerce-actions>
                                    <simple-commerce-action-item
                                        type="standard"
                                        text="Duplicate"
                                        action="{{ cp_route('products.duplicate', ['product' => $product]) }}"
                                    ></simple-commerce-action-item>
                                    <simple-commerce-action-item
                                        type="standard"
                                        text="Edit"
                                        action="{{ $product->editUrl() }}"
                                    ></simple-commerce-action-item>
                                    <simple-commerce-action-item
                                        type="delete"
                                        text="Delete"
                                        action="{{ $product->deleteUrl() }}"
                                        method="delete"
                                        modal-title="Delete Product"
                                        modal-text="Are you sure you want to delete this product?"
                                    ></simple-commerce-action-item>
                                </simple-commerce-actions>
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
        </div>
    @else
        @include('statamic::partials.create-first', [
            'resource' => 'Product',
            'svg' => 'empty/collection',
            'route' => $createUrl
        ])
    @endif
@endsection
