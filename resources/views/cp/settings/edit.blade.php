@extends('statamic::layout')
@section('title', 'Simple Commerce Settings')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <publish-form
        id="settings-form"
        title="Settings"
        action="{{ cp_route('settings.update') }}"
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
    ></publish-form>
@endsection
