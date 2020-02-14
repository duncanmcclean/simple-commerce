@extends('statamic::layout')
@section('title', 'Tax Rates')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="content">
        <h1 class="mb">Tax Rates</h1>
    </div>

    <tax-rate-settings
            index-endpoint="{{ cp_route('commerce-api.tax-rates.index') }}"
            store-endpoint="{{ cp_route('commerce-api.tax-rates.store') }}"
            initial-blueprint='@json($blueprint)'
            initial-meta='@json($meta)'
            initial-values='@json($values)'
    ></tax-rate-settings>
@endsection
