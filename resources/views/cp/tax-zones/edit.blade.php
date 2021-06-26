@extends('statamic::layout')
@section('title', 'Edit Tax Zone: ' . $taxZone->name())
@section('wrapper_class', 'max-w-xl')

@section('content')
    <script src="//unpkg.com/alpinejs" defer></script>

    <form x-data="{ country: '{{ $taxZone->country()['iso'] }}' }" action="{{ cp_route('simple-commerce.tax-zones.store') }}" method="POST">
        @csrf

        <header class="mb-3">
            <div class="flex items-center justify-between">
                <h1>Edit Tax Zone: {{ $taxZone->name() }}</h1>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </header>

        <div class="publish-form card p-0 flex flex-wrap">
            <div class="form-group w-full">
                <label class="block mb-1">Name</label>
                <input type="text" name="name" autofocus="autofocus" class="input-text" value="{{ $taxZone->name() }}">
            </div>

            <div class="form-group w-full">
                <label class="block mb-1">Country</label>
                <select name="country" class="input-text" value="{{ $taxZone->country()['iso'] }}" x-model="country">
                    @foreach($countries as $country)
                        <option value="{{ $country['iso'] }}">{{ $country['name'] }}</option>
                    @endforeach
                </select>
            </div>

            @foreach ($countries as $country)
                @php
                    $countryRegions = collect($regions)->where('country_iso', $country['iso'])->toArray();
                @endphp

                <div x-show="country === '{{ $country['iso'] }}'" class="form-group w-full">
                    <label class="block mb-1">Region</label>
                    <select name="region" class="input-text" @if($taxZone->region()) value="{{ $taxZone->region()['id'] }}" @endif>
                        @foreach($countryRegions as $region)
                            <option value="{{ $region['id'] }}">{{ $region['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
    </form>
@endsection
