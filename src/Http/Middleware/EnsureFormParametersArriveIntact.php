<?php

namespace DuncanMcClean\SimpleCommerce\Http\Middleware;

use Closure;
use DuncanMcClean\SimpleCommerce\Exceptions\InvalidFormParametersException;
use Illuminate\Contracts\Encryption\DecryptException;

class EnsureFormParametersArriveIntact
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
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

        // If the validation of form parameters is disabled, we want to take the
        // user's input and encrypt it, so it can be used later in this middleware.
        if (config('simple-commerce.disable_form_parameter_validation')) {
            $request->merge([
                '_request' => encrypt($request->get('_request') ?? $request->header('referer') ?? '/'),
                '_error_redirect' => encrypt($request->get('_error_redirect') ?? $request->header('referer') ?? '/'),
                '_redirect' => encrypt($request->get('_redirect') ?? 'Empty'),
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
