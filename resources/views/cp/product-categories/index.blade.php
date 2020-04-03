@extends('statamic::layout')
@section('title', 'Product Categories')

@section('content')
    <div class="flex items-center justify-between mb-3">
        <h1 class="flex-1">Product Categories</h1>
        <a class="btn-primary" href="{{ $createUrl }}">Create Category</a>
    </div>

    @if ($categories->count())
        <div class="card p-0">
            <table class="bg-white data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th class="actions-column"></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td><a href="{{ $category->showUrl() }}">{{ $category->title }}</a></td>

                            <td>{{ $category->slug }}</td>

                            <td class="flex justify-end">
                                <simple-commerce-actions>
                                    <simple-commerce-action-item
                                        type="standard"
                                        text="View Products"
                                        action="{{ $category->showUrl() }}"
                                    ></simple-commerce-action-item>
                                    <simple-commerce-action-item
                                        type="standard"
                                        text="Edit"
                                        action="{{ $category->editUrl() }}"
                                    ></simple-commerce-action-item>
                                    <simple-commerce-action-item
                                        type="delete"
                                        text="Delete"
                                        action="{{ $category->deleteUrl() }}"
                                        method="delete"
                                        modal-title="Delete Product Category"
                                        modal-text="Are you sure you want to delete this category?"
                                    ></simple-commerce-action-item>
                                </simple-commerce-actions>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($categories->hasMorePages())
                <div class="w-full flex mt-3">
                    <div class="flex-1"></div>

                    <ul class="flex justify-center items-center list-reset">
                        @if($categories->previousPageUrl())
                            <li class="mx-1">
                                <a href="{{ $categories->previousPageUrl() }}"><span>&laquo;</span></a>
                            </li>
                        @endif

                        @foreach($categories->links()->elements[0] as $number => $link)
                            <li class="mx-1 @if($number === $categories->currentPage()) font-bold @endif">
                                <a href="{{ $link }}">{{ $number }}</a>
                            </li>
                        @endforeach

                        @if($categories->nextPageUrl())
                            <li class="mx-1">
                                <a href="{{ $categories->nextPageUrl() }}">
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
            'resource' => 'Product Category',
            'svg' => 'empty/collection',
            'route' => $createUrl
        ])
    @endif
@endsection
