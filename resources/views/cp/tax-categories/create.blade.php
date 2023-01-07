@extends('statamic::layout')
@section('title', __('Create Tax Category'))
@section('wrapper_class', 'max-w-xl')

@section('content')
    <form action="{{ cp_route('simple-commerce.tax-categories.store') }}" method="POST">
        @csrf

        @include('simple-commerce::cp.partials.breadcrumbs', [
            'title' => __('Tax Categories'),
            'url' => cp_route('simple-commerce.tax-categories.index'),
        ])

        <header class="mb-3">
            <div class="flex items-center justify-between">
                <h1>{{ __('Create Tax Category') }}</h1>
                <button type="submit" class="btn-primary">{{ __('Save') }}</button>
            </div>
        </header>

        <div class="publish-form card p-0 flex flex-wrap">
            <div class="form-group w-full">
                <label class="block mb-1">{{ __('Name') }} <i class="required">*</i></label>
                <input type="text" name="name" autofocus="autofocus" class="input-text" value="{{ old('name') }}">

                @include('simple-commerce::cp.partials.error', ['name' => 'name'])
            </div>

            <div class="form-group w-full">
                <label class="block mb-1">{{ __('Description') }}</label>
                <textarea name="description" cols="30" rows="5" class="input-text">{{ old('description') }}</textarea>

                @include('simple-commerce::cp.partials.error', ['name' => 'description'])
            </div>
        </div>
    </form>
@endsection
