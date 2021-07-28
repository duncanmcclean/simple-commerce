@extends('statamic::layout')
@section('title', "Tax Zone: {$taxZone->name()}")
@section('wrapper_class', 'max-w-xl')

@section('content')
    <form action="{{ $taxZone->updateUrl() }}" method="POST">
        @csrf

        @include('simple-commerce::cp.partials.breadcrumbs', [
            'title' => __('Tax Zones'),
            'url' => cp_route('simple-commerce.tax-zones.index'),
        ])

        <header class="mb-3">
            <div class="flex items-center justify-between">
                <h1>{{ $taxZone->name() }}</h1>
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
                <select name="country" class="input-text" value="{{ $taxZone->country()['iso'] }}">
                    @foreach($countries as $country)
                        <option
                            value="{{ $country['iso'] }}"
                            @if($country['iso'] === $taxZone->country()['iso']) selected @endif
                        >
                            {{ $country['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <input
                id="regionInput"
                type="hidden"
                name="region"
                @if($taxZone->region()) value="{{ $taxZone->region()['id'] }}" @endif
            >

            <region-selector
                @if($taxZone->region()) value="{{ $taxZone->region()['id'] }}" @endif
            ></region-selector>
        </div>
    </form>
@endsection
