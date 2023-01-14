@extends('statamic::layout')
@section('title', __('Create Tax Rate'))
@section('wrapper_class', 'max-w-xl')

@section('content')
    <form action="{{ cp_route('simple-commerce.tax-rates.store') }}" method="POST">
        @csrf
        <input type="hidden" name="category" value="{{ $taxCategory->id() }}">

        @include('simple-commerce::cp.partials.breadcrumbs', [
            'title' => __('Tax Rates'),
            'url' => cp_route('simple-commerce.tax-rates.index'),
        ])

        <header class="mb-3">
            <div class="flex items-center justify-between">
                <h1>{{ __('Create Tax Rate') }}: {{ $taxCategory->name() }}</h1>
                <button type="submit" class="btn-primary">{{ __('Save') }}</button>
            </div>
        </header>

        <div class="publish-form card p-0 flex flex-wrap">
            <div class="flex flex-col md:flex-row items-center w-full">
                <div class="form-group w-full md:w-1/2">
                    <label class="block mb-1">Na{{ __('Name') }}me <i class="required">*</i></label>

                    <input type="text" name="name" autofocus="autofocus" class="input-text" value="{{ old('name') }}">

                    @include('simple-commerce::cp.partials.error', ['name' => 'name'])
                </div>

                <div class="form-group w-full md:w-1/2">
                    <label class="block mb-1">{{ __('Rate') }} <i class="required">*</i></label>

                    <div class="input-group">
                        <input type="number" name="rate" class="input-text" value="{{ old('rate') }}">
                        <div class="input-group-append">%</div>
                    </div>

                    @include('simple-commerce::cp.partials.error', ['name' => 'rate'])
                </div>
            </div>

            <div class="form-group w-full">
                <label class="block mb-1">{{ __('Tax Zone') }} <i class="required">*</i></label>

                <select name="zone" class="input-text" required>
                    <option selected>{{ __('Please select') }}</option>
                    @foreach($taxZones as $taxZone)
                        <option value="{{ $taxZone->id() }}" @if($taxZone->id() === old('zone')) selected @endif>{{ $taxZone->name() }}</option>
                    @endforeach
                </select>

                @include('simple-commerce::cp.partials.error', ['name' => 'zone'])
            </div>

            <div class="form-group w-full md:w-1/2">
                <label class="block mb-1">{{ __('Prices include tax?') }}</label>

                <div class="help-block -mt-1">
                    {{ __('Do product prices include already include tax?') }}
                </div>

                <input type="hidden" name="include_in_price" value="{{ old('include_in_price') ?? 'false' }}">

                <button
                    id="includeInPriceToggle"
                    type="button"
                    class="toggle-container @if(old('include_in_price')) on @endif"
                    onclick="toggle()"
                >
                    <div class="toggle-slider">
                        <div class="toggle-knob" tabindex="0" ref="knob" />
                    </div>
                </button>

                @include('simple-commerce::cp.partials.error', ['name' => 'include_in_price'])
            </div>
        </div>
    </form>

    <script>
        function toggle(e) {
            let toggleButton = document.getElementById('includeInPriceToggle')
            let includedInPriceInput = document.getElementsByName('include_in_price')[0]

            includedInPriceInput.value = ! (includedInPriceInput.value == 'true')
            toggleButton.classList.toggle('on')
        }
    </script>
@endsection
