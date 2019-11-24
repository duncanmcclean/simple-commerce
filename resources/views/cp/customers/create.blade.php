@extends('statamic::layout')
@section('title', 'Create Customer')

@section('content')
    <publish-form
            title="Create Customer"
            action="{{ cp_route('customers.store') }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>
@endsection
