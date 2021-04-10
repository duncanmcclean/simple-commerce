<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Console\Processes\Composer;
use Statamic\UpdateScripts\UpdateScript;

class UpdateConfig extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.3.0');
    }

    public function update()
    {
        // 1. Gateways\StripeGateway -> Gateways\Builtin\StripeGateway
        // 2. New `notifications` format
        // 3. Add `content` array & remove `collections`/`taxonomies` array

        $this
            ->handleGatewayNamespaceChange()
            ->handleNewNotificationsFormat()
            ->handleNewContentArray()
            ->handleRemovingCollectionAndTaxonomyArrays();
    }

    protected function handleGatewayNamespaceChange(): self
    {
        $contents = Str::of(File::get(config_path('simple-commerce.php')))
            ->replace("DoubleThreeDigital\\SimpleCommerce\\Gateways\\DummyGateway", "DoubleThreeDigital\\SimpleCommerce\\Gateways\\Builtin\\DummyGateway")
            ->replace("DoubleThreeDigital\\SimpleCommerce\\Gateways\\MollieGateway", "DoubleThreeDigital\\SimpleCommerce\\Gateways\\Builtin\\MollieGateway")
            ->replace("DoubleThreeDigital\\SimpleCommerce\\Gateways\\StripeGateway", "DoubleThreeDigital\\SimpleCommerce\\Gateways\\Builtin\\StripeGateway")
            ->__toString();

        File::put(config_path('simple-commerce.php'), $contents);

        $this->console()->info("Updated gateways config");

        return $this;
    }

    protected function handleNewNotificationsFormat(): self
    {
        //

        return $this;
    }

    protected function handleNewContentArray(): self
    {
        //

        return $this;
    }

    protected function handleRemovingCollectionAndTaxonomyArrays(): self
    {
        //

        return $this;
    }
}
