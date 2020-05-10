<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

use Barryvdh\DomPDF\Facade as PDF;
use DoubleThreeDigital\SimpleCommerce\Models\Order;

class GeneratesReceipt
{
    public function generate(Order $order, bool $storagePath = false)
    {
        // TODO: document this

        $disk = config('filesystems.disks.public');
        $filename = now()->timestamp.$order->id;

        PDF::loadView('simple-commerce::receipt', $order->templatePrep())
            ->save($disk['root']."/$filename.pdf");

        return $storagePath ? $disk['root']."/$filename.pdf" : $disk['url']."/$filename.pdf";
    }
}