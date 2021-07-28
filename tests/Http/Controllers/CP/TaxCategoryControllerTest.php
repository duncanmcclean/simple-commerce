<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory;
use Statamic\Facades\User;
use Illuminate\Support\Facades\File;

class TaxCategoryControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        collect(File::allFiles(base_path('content/simple-commerce/tax-categories')))
            ->each(function ($file) {
                File::delete($file);
            });
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
            ->get('/cp/simple-commerce/tax-categories')
            ->assertOk()
            ->assertSee('General');
    }

    /** @test */
    public function can_create_tax_category()
    {
        $this
            ->actingAs($this->user())
            ->get('/cp/simple-commerce/tax-categories/create')
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
            ->post('/cp/simple-commerce/tax-categories/create', [
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
            ->get('/cp/simple-commerce/tax-categories/hmmmm/edit')
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
            ->post('/cp/simple-commerce/tax-categories/whoop/edit', [
                'name' => 'Whoopsie',
                'description' => 'Whoopsie whoopsie whoopsie!',
            ])
            ->assertRedirect('/cp/simple-commerce/tax-categories/whoop/edit');
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
            ->delete('/cp/simple-commerce/tax-categories/birthday/delete')
            ->assertRedirect('/cp/simple-commerce/tax-categories');
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
