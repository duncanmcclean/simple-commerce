@extends('statamic::layout')
@section('title', 'Product Categories')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="flex items-center mb-3">
        <h1 class="flex-1">
            Product Categories
        </h1>

        <a class="btn btn-primary" href="{{ $createUrl }}">
            Create Category
        </a>
    </div>

    @if ($categories->count())
        <table class="bg-white data-table">
            <thead>
            <tr>
                <th>Title</th>
                <th>Slug</th>
                <th></th>
            </tr>
            </thead>

            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>
                            <a href="{{ $category->showUrl() }}">{{ $category->title }}</a>
                        </td>

                        <td>
                            {{ $category->slug }}
                        </td>

                        <td class="flex justify-end">
                            <dropdown-list>
                                <dropdown-item tex="Products" redirect="{{ $category->showUrl() }}"></dropdown-item>
                                <dropdown-item text="Edit" redirect="{{ $category->editUrl() }}"></dropdown-item>
                                <dropdown-item class="warning" text="Delete" redirect="{{ $category->deleteUrl() }}"></dropdown-item>
                            </dropdown-list>
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
    @else
        @component('statamic::partials.create-first', [
            'resource' => 'Product Category',
            'svg' => 'empty/collection',
        ])
            <a
                    class="btn btn-primary"
                    href="{{ $createUrl }}"
            >
                Create Product Category
            </a>
        @endcomponent
    @endif
@endsection
