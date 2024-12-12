@extends('statamic::layout')
@section('title', __('Tax Zones'))

@section('content')
    @unless($taxZones->isEmpty())

        <div class="flex mb-6">
            <h1 class="flex-1">{{ __('Tax Zones') }}</h1>
            <a href="{{ cp_route('simple-commerce.tax-zones.create') }}" class="btn-primary">{{ __('Create Tax Zone') }}</a>
        </div>

        <tax-class-listing
            :initial-rows="{{ json_encode($taxZones) }}"
            :initial-columns="{{ json_encode($columns) }}">
        </tax-class-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => __('Tax Zones'),
            'description' => __('simple-commerce::messages.tax_zones_intro'),
            'svg' => 'empty/fieldsets',
            'button_text' => __('Create Tax Zones'),
            'button_url' => cp_route('simple-commerce.tax-zones.create'),
        ])

    @endunless
@endsection
