@extends('statamic::layout')
@section('title', __('Dashboard'))

@section('content')

    <div class="widgets flex flex-wrap -mx-2 py-1">
        <div class="widget w-full  mb-4 px-2"><div class="card p-0 content">
{{--                <div class="bg-pink rounded-t px-3 py-2">--}}
{{--                    <p class="font-bold text-white text-lg">Welcome to the Commerce Addon!</p>--}}
{{--                    <p class="text-white text-sm"></p>--}}
{{--                </div>--}}

                <div class="p-3">
                    <h1>Getting Started with Statamic</h1>
                    <p>To begin building your new Statamic site, we recommend starting with these steps.</p>
                </div>
            </div>
        </div>
    </div>

@stop
