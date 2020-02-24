@extends('statamic::layout')
@section('title', 'Simple Commerce Settings')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="content">
        <h1 class="mb">Settings</h1>
    </div>

    <div class="card p-0 content">
        <div class="flex flex-wrap">
            @foreach ($settings as $setting)
                <a href="{{ $setting['url'] }}" class="w-full lg:w-1/2 p-3 border-t md:flex items-start hover:bg-grey-10 group {{ $loop->odd ? 'lg:border-r' : '' }}">
                    <div class="h-8 w-8 mr-2 hidden md:block text-grey-80">
                        @svg($setting['icon'])
                    </div>
                    <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                        <h3 class="mb-1 text-blue group-hover:text-grey-80">{{ $setting['title'] }} &rarr;</h3>
                        <p>{{ $setting['description'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endsection
