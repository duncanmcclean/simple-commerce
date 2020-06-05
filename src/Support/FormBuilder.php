<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

class FormBuilder
{
    public $form;

    /**
     * @param string $form
     * @param array  $params
     * @param string $contents
     *
     * @return string
     */
    public function build(string $form, array $params, string $contents): string
    {
        $this->form = $form;

        return $this->{$form}($params, $contents);
    }

    /**
     * @param array  $params
     * @param string $contents
     *
     * @return string
     */
    public function cartCreate(array $params, string $contents): string
    {
        return $this->compose(route('statamic.simple-commerce.cart.store'), 'POST', $params, $contents);
    }

    /**
     * @param array  $params
     * @param string $contents
     *
     * @return string
     */
    public function cartUpdate(array $params, string $contents): string
    {
        return $this->compose(route('statamic.simple-commerce.cart.update'), 'POST', $params, $contents);
    }

    /**
     * @param array  $params
     * @param string $contents
     *
     * @return string
     */
    public function cartDelete(array $params, string $contents): string
    {
        return $this->compose(route('statamic.simple-commerce.cart.destroy'), 'POST', $params, $contents);
    }

    /**
     * @param array  $params
     * @param string $contents
     *
     * @return string
     */
    public function checkout(array $params, string $contents): string
    {
        return $this->compose(route('statamic.simple-commerce.checkout.store'), 'POST', $params, $contents);
    }

    /**
     * @param array  $params
     * @param string $contents
     *
     * @return string
     */
    public function redeemCoupon(array $params, string $contents): string
    {
        return $this->compose(route('statamic.simple-commerce.redeem-coupon'), 'POST', $params, $contents);
    }

    /**
     * @param string $action
     * @param string $method
     * @param array  $params
     * @param string $contents
     *
     * @return string
     */
    protected function compose(string $action, string $method, array $params, string $contents): string
    {
        $errors = $this->getErrorBag();

        $body = $contents;
        $body .= csrf_field();

        if (array_key_exists('redirect', $params)) {
            $body .= '<input type="hidden" name="_redirect" value="'.$params['redirect'].'">';
            unset($params['redirect']);
        }

        $formParameters = '';
        unset($params['for'], $params['in']);

        foreach ($params as $key => $value) {
            $formParameters .= ' '.$key.'="'.$value.'" ';
        }

        return '<form action="'.$action.'" method="'.$method.'" '.$formParameters.'>'.$body.'</form>';
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return session()->has('errors') ? session()->get('errors')->hasBag('form.'.$this->form) : false;
    }

    /**
     * @return mixed
     */
    public function getErrorBag()
    {
        if ($this->hasErrors()) {
            return session('errors')->getBag('form.'.$this->form);
        }
    }
}
