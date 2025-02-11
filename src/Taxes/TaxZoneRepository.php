<?php

namespace DuncanMcClean\SimpleCommerce\Taxes;

use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxZone;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxZoneRepository as Contract;
use DuncanMcClean\SimpleCommerce\Facades\TaxClass;
use DuncanMcClean\SimpleCommerce\Rules\UniqueTaxZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Blueprint;
use Statamic\Facades\YAML;

class TaxZoneRepository implements Contract
{
    public function all(): Collection
    {
        if (! File::exists($this->getPath())) {
            return collect();
        }

        $parse = YAML::file($this->getPath())->parse();

        return collect($parse)->map(function ($taxZone, $handle) {
            return $this->make()->handle($handle)->data($taxZone);
        });
    }

    public function find($handle): ?TaxZone
    {
        return $this->all()->firstWhere('handle', $handle);
    }

    public function make(): TaxZone
    {
        return app(TaxZone::class);
    }

    public function save($taxZone): bool
    {
        File::ensureDirectoryExists(dirname($this->getPath()));

        $data = $this->all()
            ->mapWithKeys(fn ($taxZone) => [$taxZone->handle() => $taxZone->fileData()])
            ->put($taxZone->handle(), $taxZone->fileData())
            ->all();

        $contents = YAML::dump($data);

        return File::put($this->getPath(), $contents);
    }

    public function delete($id): bool
    {
        $data = $this->all()
            ->reject(fn ($taxZone) => $taxZone->handle() === $id)
            ->mapWithKeys(fn ($taxZone) => [$taxZone->handle() => $taxZone->fileData()])
            ->all();

        $contents = YAML::dump($data);

        return File::put($this->getPath(), $contents);
    }

    public function blueprint()
    {
        return Blueprint::make('tax-zone')->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'name',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Name'),
                                        'validate' => 'required',
                                    ],
                                ],
                                [
                                    'handle' => 'type',
                                    'field' => [
                                        'type' => 'select',
                                        'display' => __('Type'),
                                        'instructions' => __('simple-commerce::messages.tax_zones_type_instructions'),
                                        'options' => [
                                            'countries' => __('Limit to countries'),
                                            'states' => __('Limit to states'),
                                            'postcodes' => __('Limit to postcodes'),
                                        ],
                                        'validate' => ['required', new UniqueTaxZone],
                                    ],
                                ],
                                [
                                    'handle' => 'countries',
                                    'field' => [
                                        'type' => 'dictionary',
                                        'display' => __('Countries'),
                                        'dictionary' => 'countries',
                                        'validate' => ['required', 'min:1'],
                                    ],
                                ],
                                [
                                    'handle' => 'states',
                                    'field' => [
                                        'type' => 'state',
                                        'display' => __('States'),
                                        'from' => 'countries',
                                        'validate' => [
                                            'required_if:type,states',
                                            'min:1',
                                        ],
                                        'if' => ['type' => 'states'],
                                    ],
                                ],
                                [
                                    'handle' => 'postcodes',
                                    'field' => [
                                        'type' => 'list',
                                        'display' => __('Postcodes'),
                                        'instructions' => __('List each postcode on a new line. Supports wildcards like `G2*`.'),
                                        'rows' => 10,
                                        'validate' => [
                                            'required_if:type,postcodes',
                                            'min:1',
                                        ],
                                        'if' => ['type' => 'postcodes'],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Tax Rates'),
                            'instructions' => __('simple-commerce::messages.tax_zones_rates_instructions'),
                            'fields' => [
                                [
                                    'handle' => 'rates',
                                    'field' => [
                                        'type' => 'group',
                                        'hide_display' => true,
                                        'fullscreen' => false,
                                        'border' => false,
                                        'fields' => TaxClass::all()->map(fn ($taxClass) => [
                                            'handle' => $taxClass->handle(),
                                            'field' => [
                                                'type' => 'integer',
                                                'display' => $taxClass->get('name'),
                                                'validate' => 'min:0',
                                                'append' => '%',
                                                'width' => 50,
                                            ],
                                        ])->values()->all(),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function getPath(): string
    {
        return base_path('content/simple-commerce/tax-zones.yaml');
    }

    public static function bindings(): array
    {
        return [
            TaxZone::class => \DuncanMcClean\SimpleCommerce\Taxes\TaxZone::class,
        ];
    }
}
