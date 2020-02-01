<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\Stripe;

class StripeGateway
{
    public function __construct()
    {
        Stripe::setApiKey(config('commerce.stripe.secret'));
    }

    public function issueRefund(string $paymentIntent)
    {
        return Refund::create(['payment_intent' => $paymentIntent]);
    }

    public function setupIntent(string $amount, string $currencyIso)
    {
        return PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currencyIso,
        ]);
    }

    public function completeIntent(string $paymentMethod)
    {
        return PaymentMethod::retrieve($paymentMethod);
    }

    public function randomPaymentMethod(string $type = 'valid', int $expiryYear = 2026)
    {
        return PaymentMethod::create([
            'type' => 'card',
            'card' => [
                'number' => $this->{$type}(),
                'exp_month' => rand(1, 9),
                'exp_year' => $expiryYear,
                'cvc' => mt_rand(100, 999),
            ],
        ])['id'];
    }

    public function valid()
    {
        return collect([
            '4242424242424242',
            '4000056655665556',
            '5555555555554444',
            '2223003122003222',
        ])->random();
    }

    public function american()
    {
        return collect([
            '4000000760000002',
            '4000001240000000',
            '4012888888881881',
            '4000004840008001',
        ])->random();
    }

    public function european()
    {
        return collect([
            '4000000400000008',
            '4000000560000004',
            '4000002080000001',
            '4000007240000007',
            '4000007520000008',
            '4000007560000009',
            '4000008260000000',
            '4000058260000005',
        ])->random();
    }

    public function asiaPacific()
    {
        return collect([
            '4000000360000006',
            '4000003440000004',
            '3530111333300000',
            '4000005540000008',
            '4000001560000002',
        ])->random();
    }

    public function oneTimePaymentAuth()
    {
        return '4000002500003155';
    }

    public function oneTimePaymentAuthFailure()
    {
        return '4000008260003178';
    }

    public function everyTimePaymentAuth()
    {
        return '4000002760003184';
    }

    public function require3DSecureWithDeclinedCard()
    {
        return '4000008400001629';
    }

    public function incorrectCvc()
    {
        return '4000000000000127';
    }

    public function cardDeclined()
    {
        return '4000000000000002';
    }

    public function cardExpired()
    {
        return '4000000000000069';
    }

    public function highRisk()
    {
        return '4000000000004954';
    }
}
