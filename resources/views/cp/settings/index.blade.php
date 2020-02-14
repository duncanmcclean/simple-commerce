@extends('statamic::layout')
@section('title', 'Simple Commerce Settings')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="content">
        <h1 class="mb">Settings</h1>
    </div>

    <div class="flex flex-wrap -mx-2 mt-3">
        @foreach ($settings as $setting)
            <div class="w-full md:w-1/3 px-2 mb-4">
                <div class="card p-0 content border-t-6 border-blue">
                    <div class="p-2">
                        <h2><a href="{{ $setting['url'] }}" class="text-grey-90 hover:text-blue">{{ $setting['title'] }}</a></h2>
                        <p>{{ $setting['description'] }}</p>
                    </div>
                    <div class="flex p-2 border-t items-center">
                        <a href="{{ $setting['url'] }}" class="font-bold text-blue text-sm hover:text-grey-90">{{ $setting['title'] }} &rarr;</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="content">
        <h2>Looking for more settings?</h2>
        <p>Simple Commerce provides a load of other settings but for those you'll need to edit the <code>config/commerce.php</code> file.</p>
    </div>
@endsection
