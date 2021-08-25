@extends('statamic::layout')
@section('title', 'Tax Rates')

@section('content')
<div class="flex items-center justify-between mb-3">
    <h1 class="flex-1">Tax Rates</h1>

    @if(auth()->user()->can('create tax rates'))
        <dropdown-list class="inline-block">
            <template v-slot:trigger>
                <button class="button btn-primary flex items-center pr-2">
                    {{ __('Create Tax Rate') }}
                    <svg-icon name="chevron-down-xs" class="w-2 ml-1" />
                </button>
            </template>

            @foreach ($taxCategories as $taxCategory)
                <dropdown-item
                    redirect="{{ cp_route('simple-commerce.tax-rates.create', ['taxCategory' => $taxCategory->id()]) }}"
                >{{ $taxCategory->name() }}</dropdown-item>
            @endforeach
        </dropdown-list>
    @endif
</div>

@if ($taxRates->count())
    <div class="card p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Rate</th>
                    <th>Tax Zone</th>
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
                        <td>
                            {{ $taxRate->rate() ?? '0' }}%
                        </td>
                        <td>
                            @if ($taxRate->zone())
                                {{ $taxRate->zone()->name() }}
                            @endif
                        </td>
                        <td class="flex justify-end">
                            <dropdown-list class="mr-1">
                                @if(auth()->user()->can('edit tax rates'))
                                    <dropdown-item :text="__('Edit')" redirect="{{ $taxRate->editUrl() }}"></dropdown-item>
                                @endif

                                @if($taxRate->id() !== 'default-rate' && auth()->user()->can('delete tax rates'))
                                    <dropdown-item :text="__('Delete')" class="warning" @click="$refs.deleter.confirm()">
                                        <resource-deleter
                                            ref="deleter"
                                            resource-title="{{ $taxRate->name() }}"
                                            route="{{ $taxRate->deleteUrl() }}"
                                            :reload="true"
                                            @deleted="document.getElementById('taxRate_{{ $taxRate->id() }}').remove()"
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
        'resource' => 'Tax Rate',
        'svg' => 'empty/collection',
        'route' => cp_route('simple-commerce.tax-rates.create'),
    ])
@endif
@endsection
