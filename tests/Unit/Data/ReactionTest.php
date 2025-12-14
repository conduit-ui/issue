<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Reaction;
use ConduitUI\Issue\Data\User;

test('can create reaction from array', function () {
    $data = [
        'id' => 123,
        'content' => '+1',
        'user' => [
            'id' => 456,
            'login' => 'testuser',
            'avatar_url' => 'https://github.com/testuser.png',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'created_at' => '2023-01-01T12:00:00Z',
    ];

    $reaction = Reaction::fromArray($data);

    expect($reaction->id)->toBe(123);
    expect($reaction->content)->toBe('+1');
    expect($reaction->user)->toBeInstanceOf(User::class);
    expect($reaction->user->login)->toBe('testuser');
});

test('can convert reaction to array', function () {
    $user = new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User');

    $reaction = new Reaction(
        id: 123,
        content: '+1',
        user: $user,
        createdAt: new DateTime('2023-01-01T12:00:00Z'),
    );

    $array = $reaction->toArray();

    expect($array['id'])->toBe(123);
    expect($array['content'])->toBe('+1');
    expect($array['user'])->toBeArray();
    expect($array['user']['login'])->toBe('testuser');
});

test('supports various reaction types', function (string $content) {
    $user = new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User');

    $reaction = new Reaction(
        id: 123,
        content: $content,
        user: $user,
        createdAt: new DateTime('2023-01-01T12:00:00Z'),
    );

    expect($reaction->content)->toBe($content);
})->with([
    '+1',
    '-1',
    'laugh',
    'confused',
    'heart',
    'hooray',
    'rocket',
    'eyes',
]);
