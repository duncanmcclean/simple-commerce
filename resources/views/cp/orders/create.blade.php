@extends('statamic::layout')
@section('title', 'Create Order')

@section('content')
    <publish-form
            title="Create Order"
            action="{{ cp_route('orders.store') }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>
@endsection
