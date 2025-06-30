@use(DuncanMcClean\SimpleCommerce\SimpleCommerce)
@extends('statamic::layout')
@section('title', __('Tax Rates'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <ui-header title="{{ __('Tax Rates') }}" icon="{{ SimpleCommerce::svg('percentage') }}">
        @if(auth()->user()->can('create tax rates'))
            <ui-dropdown>
                <template #trigger>
                    <ui-button
                        text="{{ __('Create Tax Rate') }}"
                        icon-append="ui/chevron-down"
                        variant="primary"
                    ></ui-button>
                </template>

                <ui-dropdown-menu>
                    @foreach ($taxCategories as $taxCategory)
                        <ui-dropdown-item
                            href="{{ cp_route('simple-commerce.tax-rates.create', ['taxCategory' => $taxCategory->id()]) }}"
                            text="{{ __($taxCategory->name()) }}"
                        ></ui-dropdown-item>
                    @endforeach
                </ui-dropdown-menu>
            </ui-dropdown>
        @endif
    </ui-header>

    <ui-card-list heading="{{ __('Name') }}">
        @foreach($taxRates as $taxRate)
            <ui-card-list-item>
                <a href="{{ $taxRate->editUrl() }}">{{ $taxRate->name() }}</a>

                <ui-dropdown>
                    <ui-dropdown-menu>
                        @if(auth()->user()->can('edit tax rates'))
                            <ui-dropdown-item :text="__('Edit')" href="{{ $taxRate->editUrl() }}"></ui-dropdown-item>
                        @endif

                        @if($taxRate->id() !== 'default-rate' && $taxRate->id() !== 'default-shipping-rate' && auth()->user()->can('delete tax rates'))
                            <ui-dropdown-item :text="__('Delete')" class="text-red-500" @click="$refs.deleter.confirm()"></ui-dropdown-item>
                        @endif
                    </ui-dropdown-menu>
                </ui-dropdown>

                <resource-deleter
                    ref="deleter"
                    resource-title="{{ $taxRate->name() }}"
                    route="{{ $taxRate->deleteUrl() }}"
                    :reload="true"
                    @deleted="document.getElementById('taxCategory_{{ $taxRate->id() }}').remove()"
                ></resource-deleter>
            </ui-card-list-item>
        @endforeach
    </ui-card-list>

    <x-statamic::docs-callout :topic="__('Taxes')" url="https://simple-commerce.duncanmcclean.com/tax#content-standard-tax-engine" />
@endsection
