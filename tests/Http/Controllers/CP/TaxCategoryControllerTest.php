<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Statamic\Facades\User;

class TaxCategoryControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (File::exists(base_path('content/simple-commerce/tax-categories'))) {
            collect(File::allFiles(base_path('content/simple-commerce/tax-categories')))
                ->each(function ($file) {
                    File::delete($file);
                });
        }
    }

    /** @test */
    public function can_get_index()
    {
        TaxCategory::make()
            ->name('General')
            ->description('For most products (except essential items)')
            ->save();

        $this
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/tax/categories')
            ->assertOk()
            ->assertSee('General');
    }

    /** @test */
    public function can_create_tax_category()
    {
        $this
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/tax/categories/create')
            ->assertOk()
            ->assertSee('Create Tax Category')
            ->assertSee('Name')
            ->assertSee('Description');
    }

    /** @test */
    public function can_store_tax_category()
    {
        $this
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/tax/categories/create', [
                'name' => 'Special Products',
                'description' => 'Products that are very special.',
            ])
            ->assertRedirect();
    }

    /** @test */
    public function can_edit_tax_category()
    {
        TaxCategory::make()
            ->id('hmmmm')
            ->name('Hmmmm')
            ->description('Thinking about something....')
            ->save();

        $this
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/tax/categories/hmmmm/edit')
            ->assertOk()
            ->assertSee('Hmmmm')
            ->assertSee('Thinking about something....');
    }

    /** @test */
    public function can_update_tax_category()
    {
        TaxCategory::make()
            ->id('whoop')
            ->name('Whoop')
            ->description('Whoop whoop woop!')
            ->save();

        $this
            ->actingAs($this->user())
            ->post('/cp/simple-commerce/tax/categories/whoop/edit', [
                'name' => 'Whoopsie',
                'description' => 'Whoopsie whoopsie whoopsie!',
            ])
            ->assertRedirect('/cp/simple-commerce/tax/categories/whoop/edit');
    }

    /** @test */
    public function can_destroy_tax_category()
    {
        TaxCategory::make()
            ->id('birthday')
            ->name('Birthday')
            ->description('Would you guess? Its my birthday.')
            ->save();

        $this
            ->actingAs($this->user())
            ->delete('/cp/simple-commerce/tax/categories/birthday/delete')
            ->assertRedirect('/cp/simple-commerce/tax/categories');
    }

    /**
     * @test
     *
     * This test ensures that any rates belonging to this tax category
     * are cleaned up during the delete process.
     */
    public function can_destroy_tax_category_and_delete_assosiated_rates()
    {
        TaxCategory::make()
            ->id('birthday')
            ->name('Birthday')
            ->description('Would you guess? Its my birthday.')
            ->save();

        $taxZone = TaxZone::make()->id('abc')->name('UK')->country('GB');
        $taxZone->save();

        $taxRate = TaxRate::make()->id('123')->name('UK Birthday')->category('birthday')->zone('abc');
        $taxRate->save();

        $this->assertFileExists($taxRate->path());

        $this
            ->actingAs($this->user())
            ->delete('/cp/simple-commerce/tax/categories/birthday/delete')
            ->assertRedirect('/cp/simple-commerce/tax/categories');

        $this->assertFileDoesNotExist($taxRate->path());
    }

    protected function user()
    {
        return User::make()
            ->makeSuper()
            ->email('joe.bloggs@example.com')
            ->set('password', 'secret')
            ->save();
    }
}
