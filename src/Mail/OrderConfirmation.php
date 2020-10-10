<?php

namespace DoubleThreeDigital\SimpleCommerce\Mail;

use Barryvdh\DomPDF\Facade as PDFFacade;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Entry;

class OrderConfirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $orderId;

    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }

    public function build()
    {
        $order = Entry::find($this->orderId);
        $data = $order->toAugmentedArray();

        $pdf = PDFFacade::loadView('simple-commerce::receipt', $data);
        $receiptFilename = 'receipt-'.$order->id().'.pdf';

        Storage::put(($receiptFilename), $pdf->stream('receipt.pdf'));

        return $this->markdown('simple-commerce::order-confirmation')
            ->subject(__('simple-commerce::mail.order_confirmation.subject'))
            ->with('order', $data)
            ->attachFromStorage($receiptFilename);
    }
}
