<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Data\User;

test('can create comment from array', function () {
    $data = [
        'id' => 123,
        'body' => 'This is a test comment',
        'user' => [
            'id' => 456,
            'login' => 'testuser',
            'avatar_url' => 'https://github.com/testuser.png',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'created_at' => '2023-01-01T12:00:00Z',
        'updated_at' => '2023-01-02T12:00:00Z',
        'html_url' => 'https://github.com/owner/repo/issues/comments/123',
        'issue_url' => 'https://api.github.com/repos/owner/repo/issues/1',
    ];

    $comment = Comment::fromArray($data);

    expect($comment->id)->toBe(123);
    expect($comment->body)->toBe('This is a test comment');
    expect($comment->user)->toBeInstanceOf(User::class);
    expect($comment->user->login)->toBe('testuser');
    expect($comment->htmlUrl)->toBe('https://github.com/owner/repo/issues/comments/123');
    expect($comment->issueUrl)->toBe('https://api.github.com/repos/owner/repo/issues/1');
});

test('can convert comment to array', function () {
    $user = new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User');

    $comment = new Comment(
        id: 123,
        body: 'This is a test comment',
        user: $user,
        createdAt: new DateTime('2023-01-01T12:00:00Z'),
        updatedAt: new DateTime('2023-01-02T12:00:00Z'),
        htmlUrl: 'https://github.com/owner/repo/issues/comments/123',
        issueUrl: 'https://api.github.com/repos/owner/repo/issues/1',
    );

    $array = $comment->toArray();

    expect($array['id'])->toBe(123);
    expect($array['body'])->toBe('This is a test comment');
    expect($array['user'])->toBeArray();
    expect($array['user']['login'])->toBe('testuser');
    expect($array['html_url'])->toBe('https://github.com/owner/repo/issues/comments/123');
    expect($array['issue_url'])->toBe('https://api.github.com/repos/owner/repo/issues/1');
});
