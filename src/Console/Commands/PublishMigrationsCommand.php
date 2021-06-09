<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Statamic\Console\RunsInPlease;

class PublishMigrationsCommand extends Command
{
    use RunsInPlease;

    protected $name = 'sc:migrations';
    protected $description = "Publish Simple Commerce's database migrations for the Eloquent content drivers.";

    public function handle()
    {
        $this->info('Publishing migrations...');

        File::copyDirectory(__DIR__.'/../../../database/migrations', database_path('migrations'));

        if ($this->confirm("Run migrations?")) {
            Artisan::call('migrate');
        }
    }
}
