<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

class FormBuilder
{
    public $form;

    public function build(string $form, array $params, string $contents)
    {
        $this->form = $form;

        return $this->{$form}($params, $contents);
    }

    public function cartCreate(array $params, string $contents)
    {
        return $this->compose(route('statamic.simple-commerce.cart.store'), 'POST', $params, $contents);
    }

    public function cartUpdate(array $params, string $contents)
    {
        return $this->compose(route('statamic.simple-commerce.cart.update'), 'POST', $params, $contents);
    }

    public function cartDelete(array $params, string $contents)
    {
        return $this->compose(route('statamic.simple-commerce.cart.destroy'), 'POST', $params, $contents);
    }

    public function checkout(array $params, string $contents)
    {
        return $this->compose(route('statamic.simple-commerce.checkout.store'), 'POST', $params, $contents);
    }

    protected function compose(string $action, string $method, array $params, string $contents)
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

    public function hasErrors()
    {
        return session()->has('errors') ? session()->get('errors')->hasBag('form.'.$this->form) : false;
    }

    public function getErrorBag()
    {
        if ($this->hasErrors()) {
            return session('errors')->getBag('form.'.$this->form);
        }
    }
}
