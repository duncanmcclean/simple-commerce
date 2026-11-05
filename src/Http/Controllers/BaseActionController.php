<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Customers\Concerns\AuthorizesCustomerAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Http\Controllers\Controller;

class BaseActionController extends Controller
{
    use AuthorizesCustomerAccess;

    protected function cartToArray(Order $cart): array
    {
        $data = $cart->toAugmentedCollection()
            ->withRelations(['customer', 'customer_id'])
            ->withShallowNesting();

        return $this->filterCustomerInformation($cart, $data->toArray());
    }

    protected function withSuccess(Request $request, array $data = [])
    {
        if ($request->wantsJson()) {
            $data = array_merge($data, [
                'status' => 'success',
                'message' => null,
            ]);

            return response()->json($data);
        }

        if (Arr::has($data, 'is_checkout_request')) {
            $request->session()->put('simple-commerce.checkout.success', [
                'order_id' => $data['cart']['id'],
                'expiry' => now()->addMinutes(30),
                'url' => $request->_redirect,
            ]);
        }

        // The cart is only useful in a JSON response, so we'll remove it from
        // the $data array before we pass it to the view.
        if (Arr::has($data, 'cart')) {
            unset($data['cart']);
        }

        return $this->isSafeRedirect($request->_redirect)
            ? redirect($request->_redirect)->with($data)
            : back()->with($data);
    }

    protected function withErrors(Request $request, string $errorMessage)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $errorMessage,
            ]);
        }

        return $this->isSafeRedirect($request->_error_redirect)
            ? redirect($request->_error_redirect)->withErrors($errorMessage)
            : back()->withErrors($errorMessage);
    }

    private function isSafeRedirect(?string $url): bool
    {
        if (! $url) {
            return false;
        }

        return parse_url(url($url), PHP_URL_HOST) === request()->getHost();
    }
}
