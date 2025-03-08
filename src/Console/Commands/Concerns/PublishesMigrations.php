<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands\Concerns;

use Illuminate\Support\Facades\File;
use Statamic\Support\Str;

trait PublishesMigrations
{
    protected function publishMigration(
        string $stubPath,
        string $name,
        array $replacements = []
    ): void {
        $existingMigration = collect(File::allFiles(database_path('migrations')))
            ->map->getFilename()
            ->filter(fn (string $filename) => Str::contains($filename, $name))
            ->first();

        if ($existingMigration) {
            $this->components->info("Migration [database/migrations/{$existingMigration}] already exists.");

            return;
        }

        $filename = date('Y_m_d_His').'_'.$name;

        $contents = File::get($stubPath);

        foreach ($replacements as $key => $replacement) {
            $contents = str_replace($key, $replacement, $contents);
        }

        File::put(database_path('migrations/'.$filename), $contents);

        $this->components->info("Migration [database/migrations/{$filename}] published successfully.");
    }
}
