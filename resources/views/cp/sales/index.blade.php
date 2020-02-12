@extends('statamic::layout')
@section('title', 'Sales')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="flex items-center mb-3">
        <h1 class="flex-1">
            Sales
        </h1>

        <a class="btn btn-primary" href="{{ $createUrl }}">
            Create Sale
        </a>
    </div>

    @if ($sales->count())
        <table class="bg-white data-table">
            <thead>
            <tr>
                <th>Name</th>
                <th></th>
            </tr>
            </thead>

            <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>
                        <div class="flex items-center">
                            <div class="little-dot mr-1 @if($sale->is_enabled) bg-green @else bg-gray-40 @endif"></div>
                            <a href="{{ $sale->editUrl() }}">{{ $sale->name }}</a>
                        </div>
                    </td>

                    <td class="flex justify-end">
                        <dropdown-list>
                            <dropdown-item text="Edit" redirect="{{ $sale->editUrl() }}"></dropdown-item>
                            <dropdown-item class="warning" text="Delete" redirect="{{ $sale->deleteUrl() }}"></dropdown-item>
                        </dropdown-list>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        @if($sales->hasMorePages())
            <div class="w-full flex mt-3">
                <div class="flex-1"></div>

                <ul class="flex justify-center items-center list-reset">
                    @if($sales->previousPageUrl())
                        <li class="mx-1">
                            <a href="{{ $sales->previousPageUrl() }}"><span>&laquo;</span></a>
                        </li>
                    @endif

                    @foreach($sales->links()->elements[0] as $number => $link)
                        <li class="mx-1 @if($number === $sales->currentPage()) font-bold @endif">
                            <a href="{{ $link }}">{{ $number }}</a>
                        </li>
                    @endforeach

                    @if($sales->nextPageUrl())
                        <li class="mx-1">
                            <a href="{{ $sales->nextPageUrl() }}">
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
            'resource' => 'Sale',
            'svg' => 'empty/collection',
        ])
            <a
                    class="btn btn-primary"
                    href="{{ $createUrl }}"
            >
                Create Sale
            </a>
        @endcomponent
    @endif
@endsection
