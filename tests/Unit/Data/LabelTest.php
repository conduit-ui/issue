<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Label;

describe('Label', function () {
    it('can create label from array', function () {
        $data = [
            'id' => 123,
            'name' => 'bug',
            'color' => 'fc2929',
            'description' => 'Something is broken',
            'default' => false,
        ];

        $label = Label::fromArray($data);

        expect($label->id)->toBe(123);
        expect($label->name)->toBe('bug');
        expect($label->color)->toBe('fc2929');
        expect($label->description)->toBe('Something is broken');
        expect($label->default)->toBeFalse();
    });

    it('can create label from array with null description', function () {
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

    it('can convert label to array', function () {
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

    it('can convert label with null description to array', function () {
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

    it('can get hex color with hash', function () {
        $label = new Label(
            id: 123,
            name: 'bug',
            color: 'fc2929',
            description: null,
        );

        expect($label->hexColor())->toBe('#fc2929');
    });

    it('can detect light colors', function () {
        $lightLabel = new Label(
            id: 123,
            name: 'light',
            color: 'ffffff',
            description: null,
        );

        expect($lightLabel->isLightColor())->toBeTrue();
    });

    it('can detect dark colors', function () {
        $darkLabel = new Label(
            id: 123,
            name: 'dark',
            color: '000000',
            description: null,
        );

        expect($darkLabel->isLightColor())->toBeFalse();
    });
});
