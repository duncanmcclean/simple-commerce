@extends('statamic::layout')
@section('title', 'Overview')

@section('content')
    <overview
        :widgets='@json($widgets)'
        :show-entries-warning='@json($showEntriesWarning)'
    />

    @include('statamic::partials.docs-callout', [
        'topic' => 'Simple Commerce',
        'url' => 'https://simple-commerce.duncanmcclean.com/?ref=cp_overview'
    ])
@endsection
