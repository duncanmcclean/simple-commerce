<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v5_0;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\File;
use Statamic\Facades\YAML;
use Statamic\UpdateScripts\UpdateScript;

class SetDefaultNavPreferences extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('5.0.0-beta.1');
    }

    public function update()
    {
        $path = resource_path('preferences.yaml');

        $defaultPreferences = File::exists($path)
            ? YAML::file($path)->parse()
            : [];

        // If the user has already set their own preferences, we don't want to override them.
        if (isset($defaultPreferences['nav'])) {
            return;
        }

        $customerCollection = isset(SimpleCommerce::customerDriver()['collection'])
            ? SimpleCommerce::customerDriver()['collection']
            : 'customers';

        $orderCollection = isset(SimpleCommerce::orderDriver()['collection'])
            ? SimpleCommerce::orderDriver()['collection']
            : 'orders';

        $defaultPreferences['nav'] = [
            'reorder' => true,
            'sections' => [
                'top_level' => '@inherit',
                'content' => [
                    'content::collections' => [
                        'action' => '@modify',
                        'children' => [
                            "content::collections::{$customerCollection}" => '@hide',
                            "content::collections::{$orderCollection}" => '@hide',
                        ],
                    ],
                ],
                'simple_commerce' => '@inherit',
                'fields' => '@inherit',
                'tools' => '@inherit',
                'users' => '@inherit',
            ],
        ];

        File::put($path, YAML::dump($defaultPreferences));

        $this->console()->info("Simple Commerce has updated your default CP Nav preferences.");
    }
}
