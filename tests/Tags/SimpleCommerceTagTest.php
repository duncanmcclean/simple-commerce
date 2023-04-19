<?php

use DoubleThreeDigital\SimpleCommerce\Countries;
use DoubleThreeDigital\SimpleCommerce\Currencies;
use DoubleThreeDigital\SimpleCommerce\Regions;
use DoubleThreeDigital\SimpleCommerce\Tags\SimpleCommerceTag as Tag;
use DoubleThreeDigital\SimpleCommerce\Tags\SubTag;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Parse;

uses(TestCase::class);
beforeEach(function () {
    SimpleCommerceTag::register();
});


test('can get countries', function () {
    $usage = tag('{{ sc:countries }}{{ name }},{{ /sc:countries }}');

    foreach (Countries::toArray() as $country) {
        $this->assertStringContainsString($country['name'], $usage);
    }
});

test('can get countries with only parameter', function () {
    $usage = tag('{{ sc:countries only="GB|Ireland" }}{{ name }},{{ /sc:countries }}');

    $this->assertStringContainsString('United Kingdom', $usage);
    $this->assertStringContainsString('Ireland', $usage);

    $this->assertStringNotContainsString('United States', $usage);
});

test('can get countries with exclude parameter', function () {
    $usage = tag('{{ sc:countries exclude="United Kingdom|Ireland" }}{{ name }},{{ /sc:countries }}');

    $this->assertStringNotContainsString('United Kingdom', $usage);
    $this->assertStringNotContainsString('Ireland', $usage);

    $this->assertStringContainsString('United States', $usage);
});

test('can get countries with common parameter', function () {
    $usage = tag('{{ sc:countries common="IE" }}{{ name }},{{ /sc:countries }}');

    $this->assertStringContainsString('Ireland,-,', $usage);

    $this->assertStringContainsString('United Kingdom', $usage);
    $this->assertStringContainsString('United States', $usage);
});

test('can get countries with regions inside', function () {
    $usage = tag('{{ sc:countries }}{{ name }}|{{ regions limit="1" }}{{ name }}{{ /regions }},{{ /sc:countries }}');

    $this->assertStringContainsString('Austria|Burgenland', $usage);
});

test('can get currencies', function () {
    $usage = tag('{{ sc:currencies }}{{ name }},{{ /sc:currencies }}');

    foreach (Currencies::toArray() as $currency) {
        $this->assertStringContainsString($currency['name'], $usage);
    }
});

test('can get regions', function () {
    $usage = tag('{{ sc:regions }}{{ name }} ({{ country:iso }}),{{ /sc:regions }}');

    foreach (Regions::toArray() as $region) {
        $this->assertStringContainsString($region['name'], $usage);
        $this->assertStringContainsString($region['country_iso'], $usage);
    }
});

test('can get regions scoped by country', function () {
    $usage = tag('{{ sc:regions country="GB" }}{{ name }} ({{ country:iso }}),{{ /sc:regions }}');

    foreach (Regions::where('country_iso', 'GB')->toArray() as $region) {
        $this->assertStringContainsString($region['name'], $usage);
        $this->assertStringContainsString($region['country_iso'], $usage);
    }

    $this->assertStringNotContainsString('Westmeath (IE)', $usage);
});

test('can get sub tag index', function () {
    $usage = tag('{{ sc:test }}');

    $this->assertSame('This is the index method.', (string) $usage);
});

test('can get sub tag method', function () {
    $usage = tag('{{ sc:test:cheese }}');

    $this->assertSame('This is the cheese method.', (string) $usage);
});

test('can get sub tag wildcard', function () {
    $usage = tag('{{ sc:test:something }}');

    $this->assertSame('This is the wildcard method.', (string) $usage);
});

// Helpers
function tag($tag)
{
    return Parse::template($tag, []);
}

function index()
{
    return 'This is the index method.';
}

function cheese()
{
    return 'This is the cheese method.';
}

function wildcard()
{
    return 'This is the wildcard method.';
}
