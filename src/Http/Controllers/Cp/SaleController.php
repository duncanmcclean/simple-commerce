<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\Sale;
use Statamic\CP\Breadcrumbs;
use Statamic\Http\Controllers\CP\CpController;

class SaleController extends CpController
{
    public function index()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
        ]);

        $products = Sale::paginate(config('statamic.cp.pagination_size'));

        return view('commerce::cp.sales.index', [
            'crumbs' => $crumbs,
            'sales' => $products,
            'createUrl' => (new Sale())->createUrl(),
        ]);
    }

    public function create()
    {
        //
    }

    public function store()
    {
        //
    }

    public function edit()
    {
        //
    }

    public function update()
    {
        //
    }

    public function destroy()
    {
        //
    }
}
