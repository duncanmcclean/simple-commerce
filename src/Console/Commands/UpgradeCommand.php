<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Statamic\Console\Processes\Composer;
use Statamic\Console\RunsInPlease;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class UpgradeCommand extends Command
{
    use RunsInPlease;

    protected $name = 'sc:upgrade';
    protected $description = 'Upgrade Simple Commerce to v2.2';

    public function handle()
    {
        // Check if Proteus is installed
        $isProteusInstalled = Composer::create(base_path())->installedVersion('stillat/proteus');

        if (! $isProteusInstalled) {
            $this->error('To use the upgrade command please install Proteus. `composer require stillat/proteus`');
        }

        // Ask for confirmation before migrating
        $confirm = $this->confirm("Are you sure you want to upgrade Simple Commerce to v2.2?");

        if (! $confirm) {
            $this->error('Stopping upgrade command.');
            return;
        }

        // Do the upgrade
        $this->line('Beginning upgrade progress...');

        $this->upgradeConfigurationChanges();
    }

    protected function upgradeConfigurationChanges()
    {
        ConfigWriter::write('simple-commerce.cart', [
            'driver' => \DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\SessionDriver::class,
            'key' => Config::get('simple-commerce.cart_key'),
        ]);

        ConfigWriter::edit('simple-commerce')->remove('cart_key')->save();
    }
}
