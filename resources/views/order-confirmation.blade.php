Hi {{ $order['customer']->value()->data()->get('name') }}, <br>

Thanks for your purchase! This email is a confirmation of your order. We'll get in touch when your order has been dispatched. <br>

We've attached the order receipt to this email. <br>

{{ config('app.name') }}