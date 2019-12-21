@extends('statamic::layout')
@section('title', 'Create Order')

@section('content')
    <commerce-create-form inline-template>
        <publish-form
                title="Create Order"
                action="{{ cp_route('orders.store') }}"
                :blueprint='@json($blueprint)'
                :meta='@json($meta)'
                :values='@json($values)'
                @saved="redirect"
        ></publish-form>
    </commerce-create-form>
@endsection
