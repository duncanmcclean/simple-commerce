<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v2_4;

use DoubleThreeDigital\SimpleCommerce\Tax\BasicTaxEngine;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\UpdateScripts\UpdateScript;
use Stillat\Proteus\Support\Facades\ConfigWriter;
use Symfony\Component\Process\Process;

class MigrateTaxConfiguration extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.4.0-beta.1');
    }

    public function update()
    {
        if ($configurationIsCached = app()->configurationIsCached()) {
            Artisan::call('config:clear');
        }

        $this
            ->handleTaxConfig()
            ->handleTaxDocblock()
            ->tidyConfigFile();

        $this->console()->info('Tax configuration has been migrated.');

        if ($configurationIsCached) {
            Artisan::call('config:cache');
        }
    }

    protected function handleTaxConfig(): self
    {
        $defaultSite = Site::default();

        $taxRate = config("simple-commerce.sites.{$defaultSite->handle()}.tax.rate");
        $includedInPrices = config("simple-commerce.sites.{$defaultSite->handle()}.tax.included_in_prices");

        // Get rid of existing tax config
        ConfigWriter::edit('simple-commerce')
            ->remove("sites.{$defaultSite->handle()}.tax")
            ->save();

        // Set the current tax engine and tax engine config
        ConfigWriter::edit('simple-commerce')
            ->set('tax_engine', BasicTaxEngine::class)
            ->set('tax_engine_config', [
                'rate' => $taxRate,
                'included_in_prices' => $includedInPrices,
            ])
            ->save();

        return $this;
    }

    protected function handleTaxDocblock(): self
    {
        $helpComment = <<<'BLOCK'

        /*
        |--------------------------------------------------------------------------
        | Tax
        |--------------------------------------------------------------------------
        |
        | Configure the 'tax engine' you would like to use to calculate tax on
        | products & configure various tax-resource settings.
        |
        */

        BLOCK;

        $contents = Str::of(File::get(config_path('simple-commerce.php')))
            ->replace("'tax_engine' =>", $helpComment . PHP_EOL . PHP_EOL . "'tax_engine' =>")
            ->__toString();

        File::put(config_path('simple-commerce.php'), $contents);

        return $this;
    }

    protected function tidyConfigFile(): self
    {
        try {
            $process = new Process(['php-cs-fixer', 'fix', './simple-commerce.php', '--rules=@PSR2,@PhpCsFixer'], config_path());
            $process->run();
        } catch (\Exception $e) {
            //
        }

        return $this;
    }
}
