@extends('statamic::layout')
@section('title', __('Tax Rates'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="flex-1">{{ __('Tax Rates') }}</h1>

        @if(auth()->user()->can('create tax rates'))
            <dropdown-list class="inline-block">
                <template v-slot:trigger>
                    <button class="button btn-primary flex items-center pr-2">
                        {{ __('Create Tax Rate') }}
                        <svg-icon name="micro/chevron-down-xs" class="w-2 ml-1" />
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
            @include('simple-commerce::cp.partials.tax-navigation')

            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Rate') }}</th>
                        <th>{{ __('Tax Zone') }}</th>
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

                                    @if($taxRate->id() !== 'default-rate' && $taxRate->id() !== 'default-shipping-rate' && auth()->user()->can('delete tax rates'))
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
        @include('statamic::partials.empty-state', [
            'title' => __('Tax Rate'),
            'description' => __("Tax Rates allow you to set different tax rates for different categories of products, depending on the customer's location."),
            'svg' => 'empty/content',
            'button_text' => __('Create Tax Rate'),
            'button_url' => cp_route('simple-commerce.tax-rates.create'),
            'can' => auth()->user()->can('create tax rates'),
        ])
    @endif
@endsection
