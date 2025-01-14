<?php

namespace DuncanMcClean\SimpleCommerce\Support;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\SerializableClosure\SerializableClosure;

class QueuedClosure implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 5;

    protected $closure;

    public function __construct($closure)
    {
        $this->closure = new SerializableClosure($closure);
    }

    public function handle()
    {
        $closure = $this->closure->getClosure();
        $closure($this->job);
    }
}
