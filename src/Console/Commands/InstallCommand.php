<?php

namespace Damcclean\Commerce\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
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
        $this->info('First, publish all the things... (config file, views)');
        $this->callSilent('vendor:publish', [
            '--provider' => 'Damcclean\Commerce\CommerceServiceProvider',
        ]);
        $this->line('');

        $this->info('And publish the blueprints');
        $this->filesystem->copyDirectory(realpath(__DIR__.'/../../../resources/blueprints'), resource_path('blueprints'));
        $this->line('');

        if (! file_exists(realpath(base_path('content/commerce')))) {
            $this->info('Create file structure');
            $this->filesystem->makeDirectory(base_path('content/commerce'), 0755, false, true);
            $this->line('');
        }

        $this->info('All that\'s left for you to do now is update your store\'s configuration in config/commerce.php');
    }
}
