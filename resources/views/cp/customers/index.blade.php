@extends('statamic::layout')
@section('title', 'Customers')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="flex items-center mb-3">
        <h1 class="flex-1">
            Customers
        </h1>

        <a class="btn btn-primary" href="{{ $createUrl }}">
            Create Customer
        </a>
    </div>

    @if ($customers->count())
        <table class="bg-white data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Customer Since</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach($customers as $customer)
                    <tr>
                        <td>
                            <a href="{{ $customer->editUrl() }}">{{ $customer->name }}</a>
                        </td>

                        <td>
                            {{ $customer->email }}
                        </td>

                        <td>
                            {{ $customer->created_at->toFormattedDateString() }}
                        </td>

                        <td class="flex justify-end">
                            <dropdown-list>
                                <dropdown-item text="Edit" redirect="{{ $customer->editUrl() }}"></dropdown-item>
                                <dropdown-item class="warning" text="Delete" redirect="{{ $customer->deleteUrl() }}"></dropdown-item>
                            </dropdown-list>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($customers->hasMorePages())
            <div class="w-full flex mt-3">
                <div class="flex-1"></div>

                <ul class="flex justify-center items-center list-reset">
                    @if($customers->previousPageUrl())
                        <li class="mx-1">
                            <a href="{{ $customers->previousPageUrl() }}"><span>&laquo;</span></a>
                        </li>
                    @endif

                    @foreach($customers->links()->elements[0] as $number => $link)
                        <li class="mx-1 @if($number === $customers->currentPage()) font-bold @endif">
                            <a href="{{ $link }}">{{ $number }}</a>
                        </li>
                    @endforeach

                    @if($customers->nextPageUrl())
                        <li class="mx-1">
                            <a href="{{ $customers->nextPageUrl() }}">
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
            'resource' => 'Customer',
            'svg' => 'empty/collection',
        ])
            <a
                    class="btn btn-primary"
                    href="{{ $createUrl }}"
            >
                Create Customer
            </a>
        @endcomponent
    @endif
@endsection
