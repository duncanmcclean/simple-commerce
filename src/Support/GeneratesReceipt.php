<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

use Barryvdh\DomPDF\Facade as PDF;
use DoubleThreeDigital\SimpleCommerce\Models\Order;

class GeneratesReceipt
{
    /**
     * @param Order $order
     * @param bool $storagePath
     * @return string
     */
    public function generate(Order $order, bool $storagePath = false)
    {
        $disk = config("filesystems.disks.".config('simple-commerce.receipt_filesystem'));
        $filename = now()->timestamp.$order->id;

        PDF::loadView('simple-commerce::receipt', $order->templatePrep())
            ->save($disk['root']."/$filename.pdf");

        return $storagePath ? $disk['root']."/$filename.pdf" : $disk['url']."/$filename.pdf";
    }
}
