<?php

namespace DuncanMcClean\SimpleCommerce\Taxes;

use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClass;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClassRepository as Contract;
use Illuminate\Support\Collection;
use Statamic\Facades\Blueprint;
use Statamic\Facades\YAML;
use Illuminate\Support\Facades\File;

class TaxClassRepository implements Contract
{
    public function all(): Collection
    {
        if (! File::exists($this->getPath())) {
            return collect();
        }

        $parse = YAML::file($this->getPath())->parse();

        return collect($parse)->map(function ($taxClass, $handle) {
            return $this->make()->handle($handle)->data($taxClass);
        });
    }

    public function find($handle): ?TaxClass
    {
        return $this->all()->firstWhere('handle', $handle);
    }

    public function make(): TaxClass
    {
        return app(TaxClass::class);
    }

    public function save($taxClass): bool
    {
        $data = $this->all()
            ->mapWithKeys(fn ($taxClass) => [$taxClass->handle() => $taxClass->fileData()])
            ->put($taxClass->handle(), $taxClass->fileData())
            ->all();

        $contents = YAML::dump($data);

        return File::put($this->getPath(), $contents);
    }

    public function delete($id): bool
    {
        $data = $this->all()
            ->reject(fn ($taxClass) => $taxClass->handle() === $id)
            ->mapWithKeys(fn ($taxClass) => [$taxClass->handle() => $taxClass->fileData()])
            ->all();

        $contents = YAML::dump($data);

        return File::put($this->getPath(), $contents);
    }

    public function blueprint()
    {
        return Blueprint::make('tax-class')->setContents([
            'sections' => [
                'main' => [
                    'display' => 'Main',
                    'fields' => [
                        [
                            'handle' => 'name',
                            'field' => [
                                'type' => 'text',
                                'display' => __('Name'),
                                'validate' => 'required',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function getPath(): string
    {
        return base_path('content/simple-commerce/tax-classes.yaml');
    }

    public static function bindings(): array
    {
        return [
            TaxClass::class => \DuncanMcClean\SimpleCommerce\Taxes\TaxClass::class,
        ];
    }
}