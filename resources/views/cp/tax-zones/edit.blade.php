@extends('statamic::layout')
@section('title', __('Edit Tax Zone'))
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
                <button type="submit" class="btn-primary">{{ __('Save') }}</button>
            </div>
        </header>

        <div class="publish-form card p-0 flex flex-wrap">
            <div class="form-group w-full">
                <label class="block mb-1">{{ __('Name') }} <i class="required">*</i></label>
                <input type="text" name="name" autofocus="autofocus" class="input-text" value="{{ $taxZone->name() }}">

                @include('simple-commerce::cp.partials.error', ['name' => 'name'])
            </div>

            <div class="form-group w-full">
                <label class="block mb-1">{{ __('Country') }} <i class="required">*</i></label>
                <select name="country" class="input-text" @if($taxZone->country()) value="{{ $taxZone->country()['iso'] }}" @endif>
                    @if($taxZone->id() === 'everywhere')
                        <option value="" selected disabled>{{ __('Everywhere') }}</option>
                    @else
                        @foreach($countries as $country)
                            <option
                                value="{{ $country['iso'] }}"
                                @if($taxZone->country() && $country['iso'] === $taxZone->country()['iso']) selected @endif
                            >
                                {{ $country['name'] }}
                            </option>
                        @endforeach
                    @endif
                </select>

                @include('simple-commerce::cp.partials.error', ['name' => 'country'])
            </div>

            @if($taxZone->id() !== 'everywhere')
                <input
                    id="regionInput"
                    type="hidden"
                    name="region"
                    @if($taxZone->region()) value="{{ $taxZone->region()['id'] }}" @endif
                >

                <region-selector
                    @if($taxZone->region()) value="{{ $taxZone->region()['id'] }}" @endif
                ></region-selector>

                @include('simple-commerce::cp.partials.error', ['name' => 'region'])
            @endif
        </div>
    </form>
@endsection
