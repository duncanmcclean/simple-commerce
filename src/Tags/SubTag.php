<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use Statamic\Tags\Tags;

class SubTag extends Tags
{
    public function __construct(SimpleCommerceTag $passedStuff)
    {
        $this->content = $passedStuff->content;
        $this->context = $passedStuff->context;
        $this->params = $passedStuff->params;
        $this->tag = $passedStuff->tag;
        $this->method = $passedStuff->method;
        $this->parameters = $passedStuff->parameters;
        $this->parser = $passedStuff->parser;
    }
}