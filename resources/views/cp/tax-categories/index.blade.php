@extends('statamic::layout')
@section('title', 'Tax Categories')

@section('content')
<div class="flex items-center justify-between mb-3">
    <h1 class="flex-1">Tax Categories</h1>

    @if(auth()->user()->can('create tax categories'))
        <a class="btn-primary" href="{{ cp_route('simple-commerce.tax-categories.create') }}">Create Tax Category</a>
    @endif
</div>

@if ($taxCategories->count())
    <div class="card p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="actions-column"></th>
                </tr>
            </thead>

            <tbody>
                @foreach($taxCategories as $taxCategory)
                    <tr id="taxCategory_{{ $taxCategory->id() }}">
                        <td>
                            <div class="flex items-center">
                                <a href="{{ $taxCategory->editUrl() }}">{{ $taxCategory->name() }}</a>
                            </div>
                        </td>
                        <td class="flex justify-end">
                            <dropdown-list class="mr-1">
                                @if(auth()->user()->can('edit tax categories'))
                                    <dropdown-item :text="__('Edit')" redirect="{{ $taxCategory->editUrl() }}"></dropdown-item>
                                @endif

                                @if($taxCategory->id() !== 'default' && auth()->user()->can('delete tax categories'))
                                    <dropdown-item :text="__('Delete')" class="warning" @click="$refs.deleter.confirm()">
                                        <resource-deleter
                                            ref="deleter"
                                            resource-title="{{ $taxCategory->name() }}"
                                            route="{{ $taxCategory->deleteUrl() }}"
                                            :reload="true"
                                            @deleted="document.getElementById('taxCategory_{{ $taxCategory->id() }}').remove()"
                                        ></resource-deleter>
                                    </dropdown-item>
                                @endif
                            </dropdown-list>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    @include('statamic::partials.create-first', [
        'resource' => 'Tax Category',
        'svg' => 'empty/collection',
        'route' => cp_route('simple-commerce.tax-categories.create'),
    ])
@endif
@endsection
