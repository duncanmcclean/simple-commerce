<?php

namespace DoubleThreeDigital\SimpleCommerce\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Entry;
use Barryvdh\DomPDF\Facade as PDFFacade;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $orderId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $order = Entry::find($this->orderId);
        $data = $order->toAugmentedArray();

        $pdf = PDFFacade::loadView('simple-commerce::receipt', $data);
        $receiptFilename = "receipt-".$order->id().".pdf";

        Storage::put(($receiptFilename), $pdf->stream('receipt.pdf'));

        return $this->markdown('simple-commerce::order-confirmation')
            ->with('order', $data)
            ->attachFromStorage($receiptFilename);
    }
}
