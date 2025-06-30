@use(DuncanMcClean\SimpleCommerce\SimpleCommerce)
@extends('statamic::layout')
@section('title', __('Tax Categories'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <ui-header title="{{ __('Tax Categories') }}" icon="{{ SimpleCommerce::svg('percentage') }}">
        @if(auth()->user()->can('create tax categories'))
            <ui-button
                href="{{ cp_route('simple-commerce.tax-categories.create') }}"
                text="{{ __('Create Tax Category') }}"
                variant="primary"
            ></ui-button>
        @endif
    </ui-header>

    @if ($taxCategories->count())
        <ui-card-list heading="{{ __('Name') }}">
            @foreach($taxCategories as $taxCategory)
                <ui-card-list-item>
                    <a href="{{ $taxCategory->editUrl() }}">{{ $taxCategory->name() }}</a>

                    <ui-dropdown>
                        <ui-dropdown-menu>
                            @if(auth()->user()->can('edit tax categories'))
                                <ui-dropdown-item
                                    :text="__('Edit')"
                                    href="{{ $taxCategory->editUrl() }}"
                                ></ui-dropdown-item>
                            @endif

                            @if($taxCategory->id() !== 'default' && $taxCategory->id() !== 'shipping' && auth()->user()->can('delete tax categories'))
                                <ui-dropdown-item
                                    :text="__('Delete')"
                                    class="text-red-500"
                                    @click="$refs.deleter.confirm()"
                                ></ui-dropdown-item>
                            @endif
                        </ui-dropdown-menu>
                    </ui-dropdown>

                    <resource-deleter
                        ref="deleter"
                        resource-title="{{ $taxCategory->name() }}"
                        route="{{ $taxCategory->deleteUrl() }}"
                        :reload="true"
                        @deleted="document.getElementById('taxCategory_{{ $taxCategory->id() }}').remove()"
                    ></resource-deleter>
                </ui-card-list-item>
            @endforeach
        </ui-card-list>
    @else
        @include('statamic::partials.empty-state', [
            'title' => __('Tax Categories'),
            'description' => __('Tax Categories allow you to set different tax rates for different types of products. You can create tax categories and assign them to products.'),
            'svg' => 'empty/content',
            'button_text' => __('Create Tax Category'),
            'button_url' => cp_route('simple-commerce.tax-categories.create'),
            'can' => auth()->user()->can('create tax categories'),
        ])
    @endif
@endsection
