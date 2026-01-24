<?php

use App\ValueObjects\FrontendEnum;

test('can be instantiated with required properties', function () {
    $enum = new FrontendEnum(
        value: 'draft',
        label: 'Draft',
    );

    expect($enum->value)->toBe('draft');
    expect($enum->label)->toBe('Draft');
    expect($enum->name)->toBeNull();
});

test('can be instantiated with optional name property', function () {
    $enum = new FrontendEnum(
        value: 'active',
        label: 'Active',
        name: 'Active',
    );

    expect($enum->value)->toBe('active');
    expect($enum->label)->toBe('Active');
    expect($enum->name)->toBe('Active');
});

test('toArray returns array without name when name is null', function () {
    $enum = new FrontendEnum(
        value: 'draft',
        label: 'Draft',
    );

    expect($enum->toArray())->toBe([
        'value' => 'draft',
        'label' => 'Draft',
    ]);
});

test('toArray returns array with name when name is set', function () {
    $enum = new FrontendEnum(
        value: 'active',
        label: 'Active',
        name: 'Active',
    );

    expect($enum->toArray())->toBe([
        'name' => 'Active',
        'value' => 'active',
        'label' => 'Active',
    ]);
});
