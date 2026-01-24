<?php

use App\Actions\User\GenerateUniqueUserHandleAction;
use App\Models\User;

test('generates handle from first and last name', function () {
    $action = new GenerateUniqueUserHandleAction;

    $handle = $action->generate(firstName: 'John', lastName: 'Doe');

    expect($handle)->toBe('john-doe');
});

test('slugifies handle with special characters', function () {
    $action = new GenerateUniqueUserHandleAction;

    $handle = $action->generate(firstName: 'Jean-Pierre', lastName: "O'Connor");

    expect($handle)->toBe('jean-pierre-oconnor');
});

test('generates unique handle when base handle exists', function () {
    User::factory()->create(['handle' => 'john-doe']);

    $action = new GenerateUniqueUserHandleAction;

    $handle = $action->generate(firstName: 'John', lastName: 'Doe');

    expect($handle)->toStartWith('john-doe-');
    expect($handle)->not->toBe('john-doe');
});

test('handle suffix is a six digit number', function () {
    User::factory()->create(['handle' => 'john-doe']);

    $action = new GenerateUniqueUserHandleAction;

    $handle = $action->generate(firstName: 'John', lastName: 'Doe');

    $parts = explode('-', $handle);
    $suffix = end($parts);

    expect($suffix)->toMatch('/^\d{6}$/');
});

test('generates lowercase handle', function () {
    $action = new GenerateUniqueUserHandleAction;

    $handle = $action->generate(firstName: 'JOHN', lastName: 'DOE');

    expect($handle)->toBe('john-doe');
});

test('handles unicode characters', function () {
    $action = new GenerateUniqueUserHandleAction;

    $handle = $action->generate(firstName: 'José', lastName: 'García');

    expect($handle)->toBe('jose-garcia');
});
