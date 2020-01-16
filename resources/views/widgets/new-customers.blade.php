<div class="card p-0 rounded-lg pb-2">
    <div class="flex justify-between items-center p-2">
        <h2>New Customers</h2>
        <a href="{{ cp_route('customers.create') }}" class="text-blue hover:text-blue-dark text-sm">New Customer</a>
    </div>

    @if($customers->count())
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Customer Since</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach($customers as $customer)
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <a href="{{ $customer->updateUrl() }}">{{ $customer->name }}</a>
                            </div>
                        </td>

                        <td>
                            {{ $customer->email }}
                        </td>

                        <td>
                            {{ $customer->created_at->toFormattedDateString() }}
                        </td>

                        <td class="flex justify-end">
                            <dropdown-list>
                                <dropdown-item text="Edit" redirect="{{ $customer->editUrl() }}"></dropdown-item>
                                <dropdown-item class="warning" text="Delete" redirect="{{ $customer->deleteUrl() }}"></dropdown-item>
                            </dropdown-list>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="content p-2">
            <p>No customers exist.</p>
        </div>
    @endif
</div>
