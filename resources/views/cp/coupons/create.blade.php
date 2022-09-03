@extends('statamic::layout')
@section('title', 'Create Coupon')
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <form action="{{ cp_route('simple-commerce.coupons.store') }}" method="POST">
        @csrf

        @include('simple-commerce::cp.partials.breadcrumbs', [
            'title' => __('Coupons'),
            'url' => cp_route('simple-commerce.coupons.index'),
        ])

        <header class="mb-3">
            <div class="flex items-center justify-between">
                <h1>Create Coupon</h1>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </header>

        <div class="publish-form card p-0 flex flex-wrap">
            <div class="form-group w-full">
                <label class="block mb-1">Description <i class="required">*</i></label>
                <input type="text" name="description" autofocus="autofocus" class="input-text" value="{{ old('description') }}">

                @include('simple-commerce::cp.partials.error', ['name' => 'description'])
            </div>
        </div>
    </form>
@endsection
