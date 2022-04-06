<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Middleware;

use Closure;

class EnsureFormParametersArrivedIntact
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function handle($request, Closure $next)
    {
        $redirectParam = decrypt($request->get('_redirect'));
        $requestParam = decrypt($request->get('_request'));

        if (! $redirectParam || ! $requestParam) {
            throw new \Exception('Some or all request parameters could not be parsed.');
        }

        $request->merge([
            '_redirect' => $redirectParam,
            '_request' => $requestParam === 'Empty' ? null : $requestParam,
        ]);

        return $next($request);
    }
}
