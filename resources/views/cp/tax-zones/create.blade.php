@extends('statamic::layout')
@section('title', __('Create Tax Zone'))
@section('wrapper_class', 'max-w-xl')

@section('content')
    <form action="{{ cp_route('simple-commerce.tax-zones.store') }}" method="POST">
        @csrf

        @include('simple-commerce::cp.partials.breadcrumbs', [
            'title' => __('Tax Zones'),
            'url' => cp_route('simple-commerce.tax-zones.index'),
        ])

        <header class="mb-3">
            <div class="flex items-center justify-between">
                <h1>{{ __('Create Tax Zone') }}</h1>
                <button type="submit" class="btn-primary">{{ __('Save') }}</button>
            </div>
        </header>

        <div class="publish-form card p-0 flex flex-wrap">
            <div class="form-group w-full">
                <label class="block mb-1">{{ __('Name') }} <i class="required">*</i></label>
                <input type="text" name="name" autofocus="autofocus" class="input-text" value="{{ old('name') }}">

                @include('simple-commerce::cp.partials.error', ['name' => 'name'])
            </div>

            <div class="form-group w-full">
                <label class="block mb-1">{{ __('Country') }} <i class="required">*</i></label>
                <select name="country" class="input-text">
                    @foreach($countries as $country)
                        <option value="{{ $country['iso'] }}" @if(old('country') === $country['iso']) selected @endif>{{ $country['name'] }}</option>
                    @endforeach
                </select>

                @include('simple-commerce::cp.partials.error', ['name' => 'country'])
            </div>

            <input
                id="regionInput"
                type="hidden"
                name="region"
                @if(old('region')) value="{{ old('region') }}" @endif
            >

            <region-selector
                @if(old('region')) value="{{ old('region') }}" @endif
            ></region-selector>

            @include('simple-commerce::cp.partials.error', ['name' => 'region'])
        </div>
    </form>
@endsection
