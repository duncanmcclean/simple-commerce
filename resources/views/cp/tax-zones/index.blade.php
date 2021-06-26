@extends('statamic::layout')
@section('title', 'Tax Zones')

@section('content')
<div class="flex items-center justify-between mb-3">
    <h1 class="flex-1">Tax Zones</h1>

    <a class="btn-primary" href="{{ cp_route('simple-commerce.tax-zones.create') }}">Create Tax Zone</a>
</div>

@if ($taxZones->count())
    <div class="card p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th class="actions-column"></th>
                </tr>
            </thead>

            <tbody>
                @foreach($taxZones as $taxZone)
                    <tr id="taxZone_{{ $taxZone->id() }}">
                        <td>
                            <div class="flex items-center">
                                <a href="{{ $taxZone->editUrl() }}">{{ $taxZone->name() }}</a>
                            </div>
                        </td>
                        <td>{{ $taxZone->country()['name'] }}</td>
                        <td class="flex justify-end">
                            <dropdown-list class="mr-1">
                                <dropdown-item :text="__('Edit')" redirect="{{ $taxZone->editUrl() }}"></dropdown-item>
                                <dropdown-item :text="__('Delete')" class="warning" @click="$refs.deleter.confirm()">
                                    <resource-deleter
                                        ref="deleter"
                                        resource-title="{{ $taxZone->name() }}"
                                        route="{{ $taxZone->deleteUrl() }}"
                                        :reload="true"
                                        @deleted="document.getElementById('taxZone_{{ $taxZone->id() }}').remove()"
                                    ></resource-deleter>
                                </dropdown-item>
                            </dropdown-list>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    @include('statamic::partials.create-first', [
        'resource' => 'Tax Zone',
        'svg' => 'empty/collection',
        'route' => cp_route('simple-commerce.tax-zones.create'),
    ])
@endif
@endsection
