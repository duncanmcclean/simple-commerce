<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Mollie\Api\MollieApiClient;
use Mollie\Laravel\Facades\Mollie;
use Statamic\Entries\Entry;
use Statamic\Facades\Site;

class MollieGateway extends BaseGateway implements Gateway
{
    protected $mollie;

    public function name(): string
    {
        return 'Mollie';
    }

    public function prepare(GatewayPrep $data): GatewayResponse
    {
        $this->setupMollie();
        $cart = $data->cart();

        // $payment = $this->mollie->payments->create([
        //     'amount' => [
        //         'currency' => Currency::get(Site::current())['code'],
        //         'value' => substr_replace($cart->data['grand_total'], '.', -2, 0),
        //     ],
        //     'description' => 'Order: {$cart->title}',
        //     'redirectUrl' => 'http://simple-v2.test/!/simple-commerce/gateways/mollie/redirect',
        //     'webhookUrl'  => 'http://simple-v2.test/!/simple-commerce/gateways/mollie/webhook',
        // ]);

        // dd($payment);

        $payment = Mollie::api()->payments->create([
            'amount' => [
                'currency' => Currency::get(Site::current())['code'],
                'value' => (string) substr_replace($cart->data['grand_total'], '.', -2, 0),
            ],
            'description' => 'Order {$cart->title}',
            'redirectUrl' => '',
            'webhookUrl' => '',
            'metadata' => [
                'order_id' => $cart->id,
            ],
        ]);

        $payment = Mollie::api()->payments->get($payment->id);

        dd($payment);

        // redirect customer to Mollie checkout page
        return redirect($payment->getCheckoutUrl(), 303);
    }

    public function purchase(GatewayPurchase $data): GatewayResponse
    {

    }

    public function purchaseRules(): array
    {
        return [

        ];
    }

    public function getCharge(Entry $order): GatewayResponse
    {

    }

    public function refundCharge(Entry $order): GatewayResponse
    {

    }

    protected function setupMollie()
    {
        $this->mollie = new MollieApiClient();
        $this->mollie->setApiKey($this->config()['key']);
    }
}
