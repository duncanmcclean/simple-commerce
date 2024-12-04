@extends('statamic::layout')
@section('title', $breadcrumbs->title($title))

@section('content')

    <tax-zone-publish-form
        publish-container="base"
        :initial-actions="{{ json_encode($actions) }}"
        method="patch"
        initial-title="{{ $title }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        initial-listing-url="{{ cp_route('simple-commerce.tax-zones.index') }}"
    ></tax-zone-publish-form>

@endsection
