<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags\Concerns;

use Statamic\Tags\Concerns\RendersForms;

trait FormBuilder
{
    use RendersForms;

    private static $knownParams = ['redirect', 'error_redirect', 'action_needed_redirect', 'request'];

    protected function createForm(string $action, array $data = [], string $method = 'POST'): string
    {
        $html = $this->formOpen($action, $method, static::$knownParams);

        if ($this->params->get('redirect') != null) {
            $html .= $this->redirectField();
        }

        if ($this->params->get('request') != null) {
            $html .= $this->requestField();
        }

        $html .= $this->parse($this->sessionData($data));

        $html .= $this->formClose();

        return $html;
    }

    protected function sessionData($data = [])
    {
        if ($errors = $this->errors()) {
            $data['errors'] = $errors;
        }

        return $data;
    }

    private function redirectField()
    {
        return '<input type="hidden" name="_redirect" value="'.$this->params->get('redirect').'" />';
    }

    private function requestField()
    {
        return '<input type="hidden" name="_request" value="'.$this->params->get('request').'" />';
    }

    private function params(): array
    {
        return collect(static::$knownParams)->map(function ($param, $ignore) {
            if ($redirect = $this->get($param)) {
                return $params[$param] = $redirect;
            }
        })->filter()
        ->values()
        ->all();
    }

    /**
     * @return bool|string
     */
    public function errors()
    {
        if (!$this->hasErrors()) {
            return false;
        }

        $errors = [];

        foreach (session('errors')->getBag('simple-commerce')->all() as $error) {
            $errors[]['value'] = $error;
        }

        return ($this->content === '')    // If this is a single tag...
            ? !empty($errors)             // just output a boolean.
            : $errors;  // Otherwise, parse the content loop.
    }

    /**
     * Does this form have errors?
     */
    private function hasErrors(): bool
    {
        return (session()->has('errors'))
            ? session('errors')->hasBag('simple-commerce')
            : false;
    }

    /**
     * Get the errorBag from session.
     *
     * @return object
     */
    private function getErrorBag()
    {
        if ($this->hasErrors()) {
            return session('errors')->getBag('simple-commerce');
        }
    }
}
