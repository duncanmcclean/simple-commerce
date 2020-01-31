<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Console;

use DoubleThreeDigital\SimpleCommerce\Console\Commands\SeederCommand;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SeederCommandTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    public $seeder;

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new SeederCommand();
    }

    /** @test */
    public function can_seed_country_data()
    {
        $seed = $this->seeder->seedCountries();

        $this
            ->assertDatabaseHas('countries', [
                'iso' => 'US',
                'name' => 'United States',
            ])
            ->assertDatabaseHas('countries', [
                'iso' => 'NZ',
                'name' => 'New Zealand',
            ])
            ->assertDatabaseHas('countries', [
                'iso' => 'GB',
                'name' => 'United Kingdom',
            ]);
    }

    /** @test */
    public function can_seed_currency_data()
    {
        $seed = $this->seeder->seedCurrencies();

        $this
            ->assertDatabaseHas('currencies', [
                'iso' => 'CAD',
                'name' => 'Canadian Dollar',
                'symbol' => '$'
            ])
            ->assertDatabaseHas('currencies', [
                'iso' => 'LBP',
                'name' => 'Lebanese Pound',
                'symbol' => '£',
            ])
            ->assertDatabaseHas('currencies', [
                'iso' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
            ]);
    }

    /** @test */
    public function can_seed_order_status_data()
    {
        $seed = $this->seeder->seedOrderStatuses();

        $this
            ->assertDatabaseHas('order_statuses', [
                'name' => 'New',
                'slug' => 'new',
                'color' => 'green',
                'primary' => true,
            ])
            ->assertDatabaseHas('order_statuses', [
                'name' => 'Shipped',
                'slug' => 'shipped',
                'color' => 'blue',
                'primary' => false,
            ]);
    }

    /** @test */
    public function can_seed_states_data()
    {
        $usa = factory(Country::class)->create(['iso' => 'US'])->id;

        $seed = $this->seeder->seedStates();

        $this
            ->assertDatabaseHas('states', [
                'name' => 'Indiana',
                'abbreviation' => 'IN',
            ])
            ->assertDatabaseHas('states', [
                'name' => 'New York',
                'abbreviation' => 'NY',
            ])
            ->assertDatabaseHas('states', [
                'name' => 'Vermont',
                'abbreviation' => 'VT',
            ]);
    }
}
