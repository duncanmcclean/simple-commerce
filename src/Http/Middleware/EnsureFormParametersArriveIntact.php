<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Middleware;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Exceptions\InvalidFormParametersException;
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
        // In a test environment, we don't want to worry about having to pass in form
        // parameters. So, before this test, we'll set some fallbacks for the params
        // if they're not set in the request.
        if (app()->environment('testing')) {
            $request->merge([
                '_redirect' => $request->has('_redirect')
                    ? $request->get('_redirect')
                    : encrypt($request->header('referer') ?? '/'),
                '_error_redirect' => $request->has('_error_redirect')
                    ? $request->get('_error_redirect')
                    : encrypt($request->header('referer') ?? '/'),
                '_request' => $request->has('_request')
                    ? $request->get('_request')
                    : encrypt('Empty'),
            ]);
        }

        try {
            $redirectParam = decrypt($request->get('_redirect'));
            $errorRedirectParam = decrypt($request->get('_error_redirect'));
            $requestParam = decrypt($request->get('_request'));
        } catch (DecryptException $e) {
            throw new InvalidFormParametersException;
        }

        if (! $redirectParam || ! $errorRedirectParam || ! $requestParam) {
            throw new InvalidFormParametersException;
        }

        $request->merge([
            '_redirect' => $redirectParam,
            '_error_redirect' => $errorRedirectParam,
            '_request' => $requestParam === 'Empty' ? null : $requestParam,
        ]);

        return $next($request);
    }
}
