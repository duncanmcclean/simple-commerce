@extends('statamic::layout')
@section('title', 'Overview')

@section('content')
    <overview
        :widgets='@json($widgets)'
        :show-entries-warning='@json($showEntriesWarning)'
    ></overview>

    @include('statamic::partials.docs-callout', [
        'topic' => 'Simple Commerce Overview',
        'url' => 'https://simple-commerce.duncanmcclean.com/control-panel?ref=cp_overview'
    ])
@endsection
