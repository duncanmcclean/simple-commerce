@extends('statamic::layout')
@section('title', __('Tax Categories'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="flex-1">{{ __('Tax Categories') }}</h1>

        @if(auth()->user()->can('create tax categories'))
            <a class="btn-primary" href="{{ cp_route('simple-commerce.tax-categories.create') }}">{{ __('Create Tax Category') }}</a>
        @endif
    </div>

    @if ($taxCategories->count())
        <div class="card p-0">
            @include('simple-commerce::cp.partials.tax-navigation')

            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
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

                                    @if($taxCategory->id() !== 'default' && $taxCategory->id() !== 'shipping' && auth()->user()->can('delete tax categories'))
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
