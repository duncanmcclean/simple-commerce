@extends('statamic::layout')
@section('title', __('Tax Zones'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <ui-header title="{{ __('Tax Zones') }}">
        @if(auth()->user()->can('create tax zones'))
            <ui-button
                href="{{ cp_route('simple-commerce.tax-zones.create') }}"
                text="{{ __('Create Tax Zone') }}"
                variant="primary"
            ></ui-button>
        @endif
    </ui-header>

    @if ($taxZones->count())
        <ui-card-list heading="{{ __('Name') }}">
            @foreach($taxZones as $taxZone)
                <ui-card-list-item>
                    <a href="{{ $taxZone->editUrl() }}">{{ $taxZone->name() }}</a>

                    <ui-dropdown>
                        <ui-dropdown-menu>
                            @if(auth()->user()->can('edit tax categories'))
                                <ui-dropdown-item
                                    :text="__('Edit')"
                                    href="{{ $taxZone->editUrl() }}"
                                ></ui-dropdown-item>
                            @endif

                            @if($taxZone->id() !== 'everywhere' && auth()->user()->can('delete tax zones'))
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
                        resource-title="{{ $taxZone->name() }}"
                        route="{{ $taxZone->deleteUrl() }}"
                        :reload="true"
                        @deleted="document.getElementById('taxCategory_{{ $taxZone->id() }}').remove()"
                    ></resource-deleter>
                </ui-card-list-item>
            @endforeach
        </ui-card-list>
    @else
        @include('statamic::partials.empty-state', [
            'title' => __('Tax Zone'),
            'description' => __("Tax Zones allow you to define which locations should be grouped together for tax purposes. You can create tax zones and assign them to tax rates."),
            'svg' => 'empty/content',
            'button_text' => __('Create Tax Zone'),
            'button_url' => cp_route('simple-commerce.tax-zones.create'),
            'can' => auth()->user()->can('create tax zones'),
        ])
    @endif
@endsection
