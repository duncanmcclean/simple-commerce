<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Repositories;

use DoubleThreeDigital\SimpleCommerce\Repositories\CustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CustomerRepositoryTest extends TestCase
{
    /** @test */
    public function can_generate_title_and_slug_from_name_and_email()
    {
        $repo = new CustomerRepository();
        $repo->data['name'] = 'Duncan McClean';
        $repo->data['email'] = 'duncan@doublethree.digital';

        $generate = $repo->generateTitleAndSlug();

        $this->assertSame($repo->title, 'Duncan McClean <duncan@doublethree.digital>');
        $this->assertSame($repo->slug, 'duncan-at-doublethreedigital');
    }

    /** @test */
    public function can_generate_title_and_slug_from_just_email()
    {
        $repo = new CustomerRepository();
        $repo->data['email'] = 'james@example.com';

        $generate = $repo->generateTitleAndSlug();

        $this->assertSame($repo->title, ' <james@example.com>');
        $this->assertSame($repo->slug, 'james-at-examplecom');
    }
}
