<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Statamic\View\View;

class ThanksController
{
    public function __invoke()
    {
        return (new View)
            ->template('commerce.thanks')
            ->layout('layout')
            ->with([]);
    }
}
