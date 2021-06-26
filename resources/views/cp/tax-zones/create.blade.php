@extends('statamic::layout')
@section('title', 'Create Tax Zone')
@section('wrapper_class', 'max-w-xl')

@section('content')
    <script src="//unpkg.com/alpinejs" defer></script>

    <form x-data="{ country: null }" action="{{ cp_route('simple-commerce.tax-zones.store') }}" method="POST">
        @csrf

        <header class="mb-3">
            <div class="flex items-center justify-between">
                <h1>Create Tax Zone</h1>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </header>

        <div class="publish-form card p-0 flex flex-wrap">
            <div class="form-group w-full">
                <label class="block mb-1">Name</label>
                <input type="text" name="name" autofocus="autofocus" class="input-text">
            </div>

            <div class="form-group w-full">
                <label class="block mb-1">Country</label>
                <select name="country" class="input-text" x-model="country">
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
                    <select name="region" class="input-text">
                        @foreach($countryRegions as $region)
                            <option value="{{ $region['id'] }}">{{ $region['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
    </form>
@endsection
