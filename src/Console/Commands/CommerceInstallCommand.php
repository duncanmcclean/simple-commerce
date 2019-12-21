<?php

namespace Damcclean\Commerce\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CommerceInstallCommand extends Command
{
    protected $signature = 'commerce:install';
    protected $description = 'Guides you through the installation of Commerce for Statamic.';

    public function __construct()
    {
        parent::__construct();
        $this->filesystem = new Filesystem();
    }

    public function handle()
    {
        $this->info('⚙️ First things first, let\'s publish your config file...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'config'
        ]);
        $this->line('');

        $this->info('And publish the blueprints');
        $this->filesystem->copyDirectory(realpath(__DIR__.'/../../../resources/blueprints'), resource_path('blueprints'));
        $this->line('');

        $this->info('All that\'s left for you to do now is update your store\'s configuration in config/commerce.php');
    }
}
