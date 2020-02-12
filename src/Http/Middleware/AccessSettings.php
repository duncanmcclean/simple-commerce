<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Middleware;

use Closure;

class AccessSettings
{
    public function handle($request, Closure $next)
    {
        if (! auth()->user()->hasPermission('edit simple commerce settings') && ! auth()->user()->isSuper()) {
            abort(401);
        }

        return $next($request);
    }
}
