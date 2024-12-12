@extends('statamic::layout')
@section('title', __('Tax Classes'))

@section('content')
    @unless($taxClasses->isEmpty())

        <div class="flex mb-6">
            <h1 class="flex-1">{{ __('Tax Classes') }}</h1>
            <a href="{{ cp_route('simple-commerce.tax-classes.create') }}" class="btn-primary">{{ __('Create Tax Class') }}</a>
        </div>

        <tax-class-listing
            :initial-rows="{{ json_encode($taxClasses) }}"
            :initial-columns="{{ json_encode($columns) }}">
        </tax-class-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => __('Tax Classes'),
            'description' => __('simple-commerce::messages.tax_class_intro'),
            'svg' => 'empty/fieldsets',
            'button_text' => __('Create Tax Class'),
            'button_url' => cp_route('simple-commerce.tax-classes.create'),
        ])

    @endunless
@endsection
