<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Label;

test('can create label from array', function () {
    $data = [
        'id' => 123,
        'name' => 'bug',
        'color' => 'fc2929',
        'description' => 'Something is broken',
    ];

    $label = Label::fromArray($data);

    expect($label->id)->toBe(123);
    expect($label->name)->toBe('bug');
    expect($label->color)->toBe('fc2929');
    expect($label->description)->toBe('Something is broken');
});

test('can create label from array with null description', function () {
    $data = [
        'id' => 123,
        'name' => 'enhancement',
        'color' => '84b6eb',
        'description' => null,
    ];

    $label = Label::fromArray($data);

    expect($label->id)->toBe(123);
    expect($label->name)->toBe('enhancement');
    expect($label->color)->toBe('84b6eb');
    expect($label->description)->toBeNull();
});

test('can convert label to array', function () {
    $label = new Label(
        id: 123,
        name: 'bug',
        color: 'fc2929',
        description: 'Something is broken',
    );

    $array = $label->toArray();

    expect($array['id'])->toBe(123);
    expect($array['name'])->toBe('bug');
    expect($array['color'])->toBe('fc2929');
    expect($array['description'])->toBe('Something is broken');
});

test('can convert label with null description to array', function () {
    $label = new Label(
        id: 123,
        name: 'enhancement',
        color: '84b6eb',
        description: null,
    );

    $array = $label->toArray();

    expect($array['id'])->toBe(123);
    expect($array['name'])->toBe('enhancement');
    expect($array['color'])->toBe('84b6eb');
    expect($array['description'])->toBeNull();
});
