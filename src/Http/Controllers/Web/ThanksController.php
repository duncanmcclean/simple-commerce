<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web;

use Statamic\View\View;

class ThanksController
{
    public function __invoke()
    {
        return (new View)
            ->template('commerce::web.thanks')
            ->layout('commerce::web.layout')
            ->with([
                'title' => 'Thanks!',
                'success' => 'Your order has been placed successfully.',
            ]);
    }
}
