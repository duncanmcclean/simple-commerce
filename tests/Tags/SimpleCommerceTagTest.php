<?php

use DuncanMcClean\SimpleCommerce\Countries;
use DuncanMcClean\SimpleCommerce\Currencies;
use DuncanMcClean\SimpleCommerce\Regions;
use DuncanMcClean\SimpleCommerce\Tests\Tags\Helpers\SimpleCommerceTag;

beforeEach(function () {
    SimpleCommerceTag::register();
});

test('can get countries', function () {
    $usage = (string) tag('{{ sc:countries }}{{ name }},{{ /sc:countries }}');

    foreach (Countries::toArray() as $country) {
        expect($usage)->toContain($country['name']);
    }
});

test('can get countries with only parameter', function () {
    $usage = (string) tag('{{ sc:countries only="GB|Ireland" }}{{ name }},{{ /sc:countries }}');

    expect($usage)->toContain('United Kingdom');
    expect($usage)->toContain('Ireland');

    $this->assertStringNotContainsString('United States', $usage);
});

test('can get countries with exclude parameter', function () {
    $usage = (string) tag('{{ sc:countries exclude="United Kingdom|Ireland" }}{{ name }},{{ /sc:countries }}');

    $this->assertStringNotContainsString('United Kingdom', $usage);
    $this->assertStringNotContainsString('Ireland', $usage);

    expect($usage)->toContain('United States');
});

test('can get countries with common parameter', function () {
    $usage = (string) tag('{{ sc:countries common="IE" }}{{ name }},{{ /sc:countries }}');

    expect($usage)->toContain('Ireland,-,');

    expect($usage)->toContain('United Kingdom');
    expect($usage)->toContain('United States');
});

test('can get countries with regions inside', function () {
    $usage = (string) tag('{{ sc:countries }}{{ name }}|{{ regions limit="1" }}{{ name }}{{ /regions }},{{ /sc:countries }}');

    expect($usage)->toContain('Austria|Burgenland');
});

test('can get currencies', function () {
    $usage = (string) tag('{{ sc:currencies }}{{ name }},{{ /sc:currencies }}');

    foreach (Currencies::toArray() as $currency) {
        expect($usage)->toContain($currency['name']);
    }
});

test('can get regions', function () {
    $usage = (string) tag('{{ sc:regions }}{{ name }} ({{ country:iso }}),{{ /sc:regions }}');

    foreach (Regions::toArray() as $region) {
        expect($usage)->toContain($region['name']);
        expect($usage)->toContain($region['country_iso']);
    }
});

test('can get regions scoped by country', function () {
    $usage = (string) tag('{{ sc:regions country="GB" }}{{ name }} ({{ country:iso }}),{{ /sc:regions }}');

    foreach (Regions::where('country_iso', 'GB')->toArray() as $region) {
        expect($usage)->toContain($region['name']);
        expect($usage)->toContain($region['country_iso']);
    }

    $this->assertStringNotContainsString('Westmeath (IE)', $usage);
});

test('can get sub tag index', function () {
    $usage = (string) tag('{{ sc:test }}');

    expect((string) $usage)->toBe('This is the index method.');
});

test('can get sub tag method', function () {
    $usage = (string) tag('{{ sc:test:cheese }}');

    expect((string) $usage)->toBe('This is the cheese method.');
});

test('can get sub tag wildcard', function () {
    $usage = (string) tag('{{ sc:test:something }}');

    expect((string) $usage)->toBe('This is the wildcard method.');
});
