<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Helpers;

use DoubleThreeDigital\SimpleCommerce\Helpers\FormBuilder;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class FormBuilderTest extends TestCase
{
    public $builder;

    public function setUp(): void
    {
        parent::setUp();

        $this->builder = new FormBuilder();
    }

    /** @test */
    public function build_method_works()
    {
        $build = $this->builder->build('checkout', ['for' => 'checkout', 'redirect' => '/thanks'], '<input type="text" name="name">');

        $this->assertIsString($build);
        $this->assertStringContainsString('/!/checkout', $build);
        $this->assertStringContainsString('<input type="hidden" name="redirect" value="/thanks">', $build);
        $this->assertStringContainsString('<input type="text" name="name">', $build);
    }

    /** @test */
    public function a_cart_create_form_can_be_built()
    {
        $form = $this->builder->cartCreate([], '
            <input type="hidden" name="product" value="2ec1c9da-6947-478a-ad7c-6fa35233d46e">
            <input type="hidden" name="variant" value="b3452f8d-36d7-43e9-aaed-0cfd964a0a98">
            <input type="number" name="quantity" value="1">
        ');

        $this->assertStringContainsString('name="product"', $form);
        $this->assertStringContainsString('name="variant"', $form);
        $this->assertStringContainsString('name="quantity"', $form);
        $this->assertStringContainsString('name="_token"', $form);
        $this->assertStringContainsString('action="http://localhost/!/simple-commerce/cart/create"', $form);
        $this->assertStringContainsString('method="POST"', $form);
    }

    /** @test */
    public function a_cart_create_form_can_be_built_with_a_redirect()
    {
        $form = $this->builder->cartCreate([
            'redirect' => '/thanks'
        ], '
            <input type="hidden" name="product" value="2ec1c9da-6947-478a-ad7c-6fa35233d46e">
            <input type="hidden" name="variant" value="b3452f8d-36d7-43e9-aaed-0cfd964a0a98">
            <input type="number" name="quantity" value="1">
        ');

        $this->assertStringContainsString('name="product"', $form);
        $this->assertStringContainsString('name="variant"', $form);
        $this->assertStringContainsString('name="quantity"', $form);
        $this->assertStringContainsString('name="_token"', $form);
        $this->assertStringContainsString('name="redirect" value="/thanks"', $form);
        $this->assertStringContainsString('action="http://localhost/!/simple-commerce/cart/create"', $form);
        $this->assertStringContainsString('method="POST"', $form);
    }

    /** @test */
    public function a_cart_create_form_can_be_built_with_form_parameters()
    {
        $form = $this->builder->cartCreate([
            'class' => 'flex flex-col'
        ], '
            <input type="hidden" name="product" value="2ec1c9da-6947-478a-ad7c-6fa35233d46e">
            <input type="hidden" name="variant" value="b3452f8d-36d7-43e9-aaed-0cfd964a0a98">
            <input type="number" name="quantity" value="1">
        ');

        $this->assertStringContainsString('name="product"', $form);
        $this->assertStringContainsString('name="variant"', $form);
        $this->assertStringContainsString('name="quantity"', $form);
        $this->assertStringContainsString('name="_token"', $form);
        $this->assertStringContainsString('class="flex flex-col"', $form);
        $this->assertStringContainsString('action="http://localhost/!/simple-commerce/cart/create"', $form);
        $this->assertStringContainsString('method="POST"', $form);
    }

    /** @test */
    public function a_cart_update_form_can_be_built()
    {
        $form = $this->builder->cartUpdate([], '
            <input type="hidden" name="item_id" value="2ec1c9da-6947-478a-ad7c-6fa35233d46e">
            <input type="number" name="quantity" value="1">
        ');

        $this->assertStringContainsString('name="item_id"', $form);
        $this->assertStringContainsString('name="quantity"', $form);
        $this->assertStringContainsString('name="_token"', $form);
        $this->assertStringContainsString('action="http://localhost/!/simple-commerce/cart/update"', $form);
        $this->assertStringContainsString('method="POST"', $form);
    }

    /** @test */
    public function a_cart_update_form_can_be_built_with_a_redirect()
    {
        $form = $this->builder->cartUpdate([
            'redirect' => '/thanks'
        ], '
            <input type="hidden" name="item_id" value="2ec1c9da-6947-478a-ad7c-6fa35233d46e">
            <input type="number" name="quantity" value="1">
        ');

        $this->assertStringContainsString('name="item_id"', $form);
        $this->assertStringContainsString('name="quantity"', $form);
        $this->assertStringContainsString('name="_token"', $form);
        $this->assertStringContainsString('name="redirect" value="/thanks"', $form);
        $this->assertStringContainsString('action="http://localhost/!/simple-commerce/cart/update"', $form);
        $this->assertStringContainsString('method="POST"', $form);
    }

    /** @test */
    public function a_cart_update_form_can_be_built_with_form_parameters()
    {
        $form = $this->builder->cartUpdate([
            'class' => 'flex flex-col'
        ], '
            <input type="hidden" name="item_id" value="2ec1c9da-6947-478a-ad7c-6fa35233d46e">
            <input type="number" name="quantity" value="1">
        ');

        $this->assertStringContainsString('name="item_id"', $form);
        $this->assertStringContainsString('name="quantity"', $form);
        $this->assertStringContainsString('name="_token"', $form);
        $this->assertStringContainsString('class="flex flex-col"', $form);
        $this->assertStringContainsString('action="http://localhost/!/simple-commerce/cart/update"', $form);
        $this->assertStringContainsString('method="POST"', $form);
    }

    /** @test */
    public function a_cart_destroy_form_can_be_built()
    {
        $form = $this->builder->cartDelete([], '
            <input type="hidden" name="item_id" value="2ec1c9da-6947-478a-ad7c-6fa35233d46e">
            <input type="number" name="quantity" value="1">
        ');

        $this->assertStringContainsString('name="item_id"', $form);
        $this->assertStringContainsString('name="quantity"', $form);
        $this->assertStringContainsString('name="_token"', $form);
        $this->assertStringContainsString('action="http://localhost/!/simple-commerce/cart/delete"', $form);
        $this->assertStringContainsString('method="POST"', $form);
    }

    /** @test */
    public function a_cart_destroy_form_can_be_built_with_a_redirect()
    {
        $form = $this->builder->cartDelete([
            'redirect' => '/thanks'
        ], '
            <input type="hidden" name="item_id" value="2ec1c9da-6947-478a-ad7c-6fa35233d46e">
            <input type="number" name="quantity" value="1">
        ');

        $this->assertStringContainsString('name="item_id"', $form);
        $this->assertStringContainsString('name="quantity"', $form);
        $this->assertStringContainsString('name="_token"', $form);
        $this->assertStringContainsString('name="redirect" value="/thanks"', $form);
        $this->assertStringContainsString('action="http://localhost/!/simple-commerce/cart/delete"', $form);
        $this->assertStringContainsString('method="POST"', $form);
    }

    /** @test */
    public function a_cart_destroy_form_can_be_built_with_form_parameters()
    {
        $form = $this->builder->cartDelete([
            'class' => 'flex flex-col'
        ], '
            <input type="hidden" name="item_id" value="2ec1c9da-6947-478a-ad7c-6fa35233d46e">
            <input type="number" name="quantity" value="1">
        ');

        $this->assertStringContainsString('name="item_id"', $form);
        $this->assertStringContainsString('name="quantity"', $form);
        $this->assertStringContainsString('name="_token"', $form);
        $this->assertStringContainsString('class="flex flex-col"', $form);
        $this->assertStringContainsString('action="http://localhost/!/simple-commerce/cart/delete"', $form);
        $this->assertStringContainsString('method="POST"', $form);
    }

    /** @test */
    public function a_checkout_form_can_be_built()
    {
        $form = $this->builder->checkout([], '
            <input type="hidden" name="name" value="Duncan McClean">
            <input type="hidden" name="email" value="duncan@example.com">
            <input type="number" name="quantity" value="1">
        ');

        $this->assertStringContainsString('name="name"', $form);
        $this->assertStringContainsString('name="email"', $form);
        $this->assertStringContainsString('name="_token"', $form);
        $this->assertStringContainsString('action="http://localhost/!/simple-commerce/checkout"', $form);
        $this->assertStringContainsString('method="POST"', $form);
    }

    /** @test */
    public function a_checkout_form_can_be_built_with_a_redirect()
    {
        $form = $this->builder->checkout([
            'redirect' => '/thanks'
        ], '
            <input type="hidden" name="name" value="Duncan McClean">
            <input type="hidden" name="email" value="duncan@example.com">
            <input type="number" name="quantity" value="1">
        ');

        $this->assertStringContainsString('name="name"', $form);
        $this->assertStringContainsString('name="email"', $form);
        $this->assertStringContainsString('name="_token"', $form);
        $this->assertStringContainsString('name="redirect" value="/thanks"', $form);
        $this->assertStringContainsString('action="http://localhost/!/simple-commerce/checkout"', $form);
        $this->assertStringContainsString('method="POST"', $form);
    }

    /** @test */
    public function a_checkout_form_can_be_built_with_form_parameters()
    {
        $form = $this->builder->checkout([
            'class' => 'flex flex-col'
        ], '
            <input type="hidden" name="name" value="Duncan McClean">
            <input type="hidden" name="email" value="duncan@example.com">
            <input type="number" name="quantity" value="1">
        ');

        $this->assertStringContainsString('name="name"', $form);
        $this->assertStringContainsString('name="email"', $form);
        $this->assertStringContainsString('name="_token"', $form);
        $this->assertStringContainsString('class="flex flex-col"', $form);
        $this->assertStringContainsString('action="http://localhost/!/simple-commerce/checkout"', $form);
        $this->assertStringContainsString('method="POST"', $form);
    }
}
