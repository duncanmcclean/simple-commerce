@extends('statamic::layout')
@section('title', 'Create Tax Category')
@section('wrapper_class', 'max-w-xl')

@section('content')
    <form action="{{ cp_route('simple-commerce.tax-categories.store') }}" method="POST">
        @csrf

        <header class="mb-3">
            <div class="flex items-center justify-between">
                <h1>Create Tax Category</h1>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </header>

        <div class="publish-form card p-0 flex flex-wrap">
            <div class="form-group w-full">
                <label class="block mb-1">Name</label>
                <input type="text" name="name" autofocus="autofocus" class="input-text">
            </div>

            <div class="form-group w-full">
                <label class="block mb-1">Description</label>
                <textarea name="description" cols="30" rows="5" class="input-text"></textarea>
            </div>
        </div>
    </form>
@endsection
