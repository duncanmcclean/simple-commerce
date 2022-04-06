<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags\Concerns;

use Illuminate\Support\Str;
use Statamic\Tags\Concerns\RendersForms;

trait FormBuilder
{
    use RendersForms;

    private static $knownParams = ['redirect', 'error_redirect', 'request'];

    protected function createForm(string $action, array $data = [], string $method = 'POST'): string
    {
        $html = $this->formOpen($action, $method, static::$knownParams);

        $html .= $this->redirectField();
        $html .= $this->errorRedirectField();
        $html .= $this->requestField();

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
        $redirect = Str::start($this->params->get('redirect', request()->path()), '/');

        return '<input type="hidden" name="_redirect" value="' . encrypt($redirect) . '" />';
    }

    private function errorRedirectField()
    {
        $errorRedirect = Str::start($this->params->get('error_redirect', request()->path()), '/');

        return '<input type="hidden" name="_error_redirect" value="' . encrypt($errorRedirect) . '" />';
    }

    private function requestField()
    {
        $request = $this->params->get('request', 'Empty');

        return '<input type="hidden" name="_request" value="' . encrypt($request) . '" />';
    }

    /**
     * @return bool|string
     */
    public function errors()
    {
        if (! $this->hasErrors()) {
            return false;
        }

        $errors = [];

        foreach (session('errors')->getBag('default')->all() as $error) {
            $errors[]['value'] = $error;
        }

        return ($this->content === '')    // If this is a single tag...
            ? ! empty($errors)             // just output a boolean.
            : $errors;  // Otherwise, parse the content loop.
    }

    /**
     * Does this form have errors?
     */
    private function hasErrors(): bool
    {
        return (session()->has('errors'))
            ? session('errors')->hasBag('default')
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
            return session('errors')->getBag('default');
        }
    }
}
