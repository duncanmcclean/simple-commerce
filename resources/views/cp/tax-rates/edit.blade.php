@extends('statamic::layout')
@section('title', "Tax Rate: {$taxRate->name()}")
@section('wrapper_class', 'max-w-xl')

@section('content')
    <form action="{{ $taxRate->updateUrl() }}" method="POST">
        @csrf
        <input type="hidden" name="category" value="{{ $taxRate->category()->id() }}">

        @include('simple-commerce::cp.partials.breadcrumbs', [
            'title' => __('Tax Rates'),
            'url' => cp_route('simple-commerce.tax-rates.index'),
        ])

        <header class="mb-3">
            <div class="flex items-center justify-between">
                <h1>{{ $taxRate->name() }}</h1>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </header>

        <div class="publish-form card p-0 flex flex-wrap">
            <div class="flex flex-col md:flex-row items-center w-full">
                <div class="form-group w-full md:w-1/2">
                    <label class="block mb-1">Name</label>
                    <input type="text" name="name" autofocus="autofocus" class="input-text" value="{{ $taxRate->name() }}" required>
                </div>

                <div class="form-group w-full md:w-1/2">
                    <label class="block mb-1">Rate</label>

                    <div class="input-group">
                        <input type="number" name="rate" class="input-text" value="{{ $taxRate->rate() }}" required>
                        <div class="input-group-append">%</div>
                    </div>
                </div>
            </div>

            <div class="form-group w-full">
                <label class="block mb-1">Tax Zone</label>
                <select name="zone" class="input-text" value="{{ $taxRate->zone()->id() }}" required>
                    @foreach($taxZones as $taxZone)
                        {{-- <option selected>Please select</option> --}}
                        <option value="{{ $taxZone->id() }}">{{ $taxZone->name() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group w-full md:w-1/2">
                <label class="block mb-1">Include in price?</label>
                <input type="hidden" name="include_in_price" value="{{ $taxRate->includeInPrice() ? 'true' : 'false' }}" required>

                <button
                    id="includeInPriceToggle"
                    type="button"
                    class="toggle-container @if($taxRate->includeInPrice()) on @endif"
                    onclick="toggle()"
                >
                    <div class="toggle-slider">
                        <div class="toggle-knob" tabindex="0" ref="knob" />
                    </div>
                </button>
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
