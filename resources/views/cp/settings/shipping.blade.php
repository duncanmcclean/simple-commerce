@extends('statamic::layout')
@section('title', 'Shipping Zones')

@section('content')
    <breadcrumbs :crumbs='@json($crumbs)'></breadcrumbs>

    <div class="content">
        <h1 class="mb">Shipping</h1>
    </div>

    <section class="border-b border-grey-40 my-2 py-2">
        <div class="content">
            <h2 class="mb text-xl">Shipping Zones</h2>
            <p>Manage the zones where you can ship orders to.</p>
        </div>

        <shipping-zone-settings
            index-endpoint="{{ cp_route('shipping-zones.index') }}"
            store-endpoint="{{ cp_route('shipping-zones.store') }}"
            initial-blueprint='@json($blueprint)'
            initial-meta='@json($meta)'
            initial-values='@json($values)'
        ></shipping-zone-settings>
    </section>
@endsection
