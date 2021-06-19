@extends('statamic::layout')
@section('title', 'Tax Rates')

@section('content')
<div class="flex items-center justify-between mb-3">
    <h1 class="flex-1">Tax Rates</h1>

    <a class="btn-primary" href="{{ cp_route('simple-commerce.tax-rates.create') }}">Create Tax Rate</a>
</div>

@if ($taxRates->count())
    <div class="card p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="actions-column"></th>
                </tr>
            </thead>

            <tbody>
                @foreach($taxRates as $taxRate)
                    <tr id="taxRate_{{ $taxRate->id() }}">
                        <td>
                            <div class="flex items-center">
                                <a href="{{ $taxRate->editUrl() }}">{{ $taxRate->name() }}</a>
                            </div>
                        </td>
                        <td class="flex justify-end">
                            <dropdown-list class="mr-1">
                                <dropdown-item :text="__('Edit')" redirect="{{ $taxRate->editUrl() }}"></dropdown-item>
                                <dropdown-item :text="__('Delete')" class="warning" @click="$refs.deleter.confirm()">
                                    <resource-deleter
                                        ref="deleter"
                                        resource-title="{{ $taxRate->name() }}"
                                        route="{{ $taxRate->deleteUrl() }}"
                                        :reload="true"
                                        @deleted="document.getElementById('taxRate_{{ $taxRate->id() }}').remove()"
                                    ></resource-deleter>
                                </dropdown-item>
                            </dropdown-list>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    @include('statamic::partials.create-first', [
        'resource' => 'Tax Rate',
        'svg' => 'empty/collection',
        'route' => cp_route('simple-commerce.tax-rates.create'),
    ])
@endif
@endsection
