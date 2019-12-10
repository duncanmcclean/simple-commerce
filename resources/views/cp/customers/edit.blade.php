@extends('statamic::layout')
@section('title', 'Edit Customer')

@section('content')
    <publish-form
            title="{{ $values['name'] }}"
            action="{{ cp_route('customers.update', ['customer' => $values['customer']]) }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
    ></publish-form>
@endsection
