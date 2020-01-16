@extends('statamic::layout')
@section('title', 'Simple Commerce Settings')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="flex items-center mb-3">
        <h1 class="flex-1">Settings</h1>

        <a class="btn btn-primary" href="#">
            Save
        </a>
    </div>

    <div class="flex justify-between">
        <div class="publish-section">
            <div class="publish-fields">
                <div class="form-group publish-field text-fieldtype field-w-full">
                    <label class="publish-field-label">
                        <span class="cursor-pointer">name</span>
                    </label>

                    <div class="flex items-center">
                        <div class="input-group">
                            <input name="name" type="text" class="input-text">
                        </div>
                    </div>
                </div>

                <text-input type="text" value="" placeholder="Address 1" />
            </div>
        </div>
    </div>
@endsection
