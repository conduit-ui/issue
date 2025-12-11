<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\User;

test('can create user from array', function () {
    $data = [
        'id' => 123,
        'login' => 'testuser',
        'avatar_url' => 'https://github.com/testuser.png',
        'html_url' => 'https://github.com/testuser',
        'type' => 'User',
    ];

    $user = User::fromArray($data);

    expect($user->id)->toBe(123);
    expect($user->login)->toBe('testuser');
    expect($user->avatarUrl)->toBe('https://github.com/testuser.png');
    expect($user->htmlUrl)->toBe('https://github.com/testuser');
    expect($user->type)->toBe('User');
});

test('can convert user to array', function () {
    $user = new User(
        id: 123,
        login: 'testuser',
        avatarUrl: 'https://github.com/testuser.png',
        htmlUrl: 'https://github.com/testuser',
        type: 'User',
    );

    $array = $user->toArray();

    expect($array['id'])->toBe(123);
    expect($array['login'])->toBe('testuser');
    expect($array['avatar_url'])->toBe('https://github.com/testuser.png');
    expect($array['html_url'])->toBe('https://github.com/testuser');
    expect($array['type'])->toBe('User');
});
