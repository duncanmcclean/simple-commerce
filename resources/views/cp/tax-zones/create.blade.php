@extends('statamic::layout')
@section('title', __('Create Tax Zone'))

@section('content')
    <base-tax-zone-create-form
        title="{{ __('Create Tax Zone') }}"
        :actions="{{ json_encode($actions) }}"
        :fieldset="{{ json_encode($blueprint) }}"
        :values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        create-another-url="{{ cp_route('simple-commerce.tax-zones.create') }}"
        listing-url="{{ cp_route('simple-commerce.tax-zones.index') }}"
    ></base-tax-zone-create-form>
@endsection
