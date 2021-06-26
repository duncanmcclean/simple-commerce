@extends('statamic::layout')
@section('title', 'Create Tax Rate')
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
                <h1>Create Tax Rate: {{ $taxCategory->name() }}</h1>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </header>

        <div class="publish-form card p-0 flex flex-wrap">
            <div class="flex flex-col md:flex-row items-center w-full">
                <div class="form-group w-full md:w-1/2">
                    <label class="block mb-1">Name</label>
                    <input type="text" name="name" autofocus="autofocus" class="input-text" required>
                </div>

                <div class="form-group w-full md:w-1/2">
                    <label class="block mb-1">Rate</label>

                    <div class="input-group">
                        <input type="number" name="rate" class="input-text" required>
                        <div class="input-group-append">%</div>
                    </div>
                </div>
            </div>

            <div class="form-group w-full">
                <label class="block mb-1">Tax Zone</label>
                <select name="zone" class="input-text" required>
                    @foreach($taxZones as $taxZone)
                        <option selected>Please select</option>
                        <option value="{{ $taxZone->id() }}">{{ $taxZone->name() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
@endsection
