<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

trait AcceptsFormRequests
{
    public function buildFormRequest(string $formRequestClass, Request $request): ?FormRequest
    {
        // If the class itself just exists, use it...
        if (class_exists($class = $formRequestClass)) {
            return new $class(
                $request->query(),
                $request->all(),
                $request->attributes(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server(),
                $request->content
            );
        }

        if (class_exists($class = "App\\Http\\Requests\\$formRequestClass")) {
            return new $class(
                $request->query(),
                $request->all(),
                $request->attributes(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server(),
                $request->content
            );
        }

        throw new \Exception("Unable to find Form Request [$formRequestClass]");
    }
}
