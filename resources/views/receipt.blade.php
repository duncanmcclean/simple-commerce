<!DOCTYPE html>
<html>
    <head>
        <title>Order #{{ $id }}</title>

        <style>
            body {
                background: #fff none;
                font-size: 12px;
            }

            .text-xl {
                font-size: 1.25rem;
            }

            .font-bold {
                font-weight: bold;
            }

            .font-semibold {
                font-weight: 600;
            }

            .mt-2 {
                margin-top: 0.5rem;
            }

            .mb-2 {
                margin-bottom: 0.5rem;
            }

            .py-2 {
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }

            .px-4 {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .table-auto {
                table-layout: auto;
            }

            .mx-auto {
                margin: auto;
            }

            .w-full {
                width: 100%;
            }

            .md:w-1/2 {
                width: 50%;
            }

            .bg-gray-100 {
                background-color: #F7FAFC;
            }

            .border {
                border-width: 1px;
            }

            .text-left {
                text-align: left;
            }
        </style>
    </head>
    <body>
        <h1>Receipt #{{ $id }}</h1>

        <table class="table-auto w-full" style="margin-top: 10px; margin-bottom: 15px;">
            <thead>
                <tr>
                    <th class="text-xl font-bold mb-2 text-left">Details</th>
                    <th class="text-xl font-bold mb-2 text-left">Shipping Address</th>
                    <th class="text-xl font-bold mb-2 text-left">Billing Address</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong class="font-bold">Customer: </strong>{{ $customer['email'] }}</td>
                    <td><strong class="font-bold">{{ $shipping_address['name'] }}</strong></td>
                    <td><strong class="font-bold">{{ $billing_address['name'] }}</strong></td>
                </tr>
                <tr>
                    <td><strong class="font-bold">Total: </strong>{{ $total }}</td>
                    <td>{{ $shipping_address['address1'] }}, {{ $shipping_address['address2'] }}, {{ $shipping_address['address3'] }}</td>
                    <td>{{ $billing_address['address1'] }}, {{ $billing_address['address2'] }}, {{ $billing_address['address3'] }}</td>
                </tr>
                <tr>
                    <td><strong class="font-bold">Amount Paid: </strong>{{ $total }}</td>
                    <td>{{ $shipping_address['city'] }}</td>
                    <td>{{ $billing_address['city'] }}</td>
                </tr>
                <tr>
                    <td><strong class="font-bold">Date: </strong>{{ $created_at }}</td>
                    <td>{{ $shipping_address['zip_code'] }}</td>
                    <td>{{ $billing_address['zip_code'] }}</td>
                </tr>
                <tr>
                    <td><strong class="font-bold"></strong></td>
                    <td>{{ $shipping_address['zip_code'] }}</td>
                    <td>{{ $billing_address['zip_code'] }}</td>
                </tr>
            </tbody>
        </table>

        <table class="table-auto w-full" style="margin-top: 20px; margin-bottom: 15px;">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2">Product</th>
                    <th class="px-4 py-2">Quantity</th>
                    <th class="px-4 py-2">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($line_items as $lineItem)
                    <tr>
                        <td class="border px-4 py-2">{{ $lineItem['variant']['name'] }} ({{ $lineItem['variant']['sku'] }})</td>
                        <td class="border px-4 py-2">
                            <div class="flex justify-center items-center">
                                {{ $lineItem['quantity'] }}
                            </div>
                        </td>
                        <td class="border px-4 py-2">{{ $lineItem['total'] }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="px-4 py-2"></td>
                    <td class="border px-4 py-2 font-semibold">Shipping</td>
                    <td class="border px-4 py-2">{{ $shipping_total }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2"></td>
                    <td class="border px-4 py-2 font-semibold">Tax</td>
                    <td class="border px-4 py-2">{{ $tax_total }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2"></td>
                    <td class="border px-4 py-2 font-semibold">Coupons</td>
                    <td class="border px-4 py-2">{{ $coupon_total }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2"></td>
                    <td class="border px-4 py-2 font-semibold">Total</td>
                    <td class="border px-4 py-2">{{ $total }}</td>
                </tr>
            </tbody>
        </table>
    </body>
</html>