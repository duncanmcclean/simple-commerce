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
