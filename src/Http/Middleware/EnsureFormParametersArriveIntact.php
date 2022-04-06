<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Middleware;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Exceptions\InvalidFormParameters;
use Illuminate\Contracts\Encryption\DecryptException;

class EnsureFormParametersArriveIntact
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
        try {
            $redirectParam = decrypt($request->get('_redirect'));
            $errorRedirectParam = decrypt($request->get('_error_redirect'));
            $requestParam = decrypt($request->get('_request'));
        } catch (DecryptException $e) {
            throw new InvalidFormParameters;
        }

        if (! $redirectParam || ! $errorRedirectParam || ! $requestParam) {
            throw new InvalidFormParameters;
        }

        $request->merge([
            '_redirect' => $redirectParam,
            '_error_redirect' => $errorRedirectParam,
            '_request' => $requestParam === 'Empty' ? null : $requestParam,
        ]);

        return $next($request);
    }
}
