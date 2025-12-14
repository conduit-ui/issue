<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Milestone;
use ConduitUI\Issue\Data\User;

test('can create milestone from array', function () {
    $data = [
        'id' => 123,
        'number' => 1,
        'title' => 'v1.0',
        'description' => 'First major release',
        'state' => 'open',
        'open_issues' => 5,
        'closed_issues' => 3,
        'created_at' => '2023-01-01T12:00:00Z',
        'updated_at' => '2023-01-02T12:00:00Z',
        'closed_at' => null,
        'due_on' => '2023-12-31T23:59:59Z',
        'html_url' => 'https://github.com/owner/repo/milestone/1',
        'creator' => [
            'id' => 101,
            'login' => 'creator',
            'avatar_url' => 'https://github.com/creator.png',
            'html_url' => 'https://github.com/creator',
            'type' => 'User',
        ],
    ];

    $milestone = Milestone::fromArray($data);

    expect($milestone->id)->toBe(123);
    expect($milestone->number)->toBe(1);
    expect($milestone->title)->toBe('v1.0');
    expect($milestone->description)->toBe('First major release');
    expect($milestone->state)->toBe('open');
    expect($milestone->openIssues)->toBe(5);
    expect($milestone->closedIssues)->toBe(3);
    expect($milestone->creator)->toBeInstanceOf(User::class);
    expect($milestone->creator->login)->toBe('creator');
    expect($milestone->closedAt)->toBeNull();
    expect($milestone->dueOn)->toBeInstanceOf(DateTime::class);
});

test('can create milestone from array with null description and due date', function () {
    $data = [
        'id' => 456,
        'number' => 2,
        'title' => 'v2.0',
        'description' => null,
        'state' => 'closed',
        'open_issues' => 0,
        'closed_issues' => 10,
        'created_at' => '2023-01-01T12:00:00Z',
        'updated_at' => '2023-06-01T12:00:00Z',
        'closed_at' => '2023-06-01T12:00:00Z',
        'due_on' => null,
        'html_url' => 'https://github.com/owner/repo/milestone/2',
        'creator' => [
            'id' => 102,
            'login' => 'maintainer',
            'avatar_url' => 'https://github.com/maintainer.png',
            'html_url' => 'https://github.com/maintainer',
            'type' => 'User',
        ],
    ];

    $milestone = Milestone::fromArray($data);

    expect($milestone->description)->toBeNull();
    expect($milestone->dueOn)->toBeNull();
    expect($milestone->state)->toBe('closed');
    expect($milestone->closedAt)->toBeInstanceOf(DateTime::class);
});

test('can convert milestone to array', function () {
    $creator = new User(101, 'creator', 'https://github.com/creator.png', 'https://github.com/creator', 'User');

    $milestone = new Milestone(
        id: 123,
        number: 1,
        title: 'v1.0',
        description: 'First major release',
        state: 'open',
        openIssues: 5,
        closedIssues: 3,
        createdAt: new DateTime('2023-01-01T12:00:00Z'),
        updatedAt: new DateTime('2023-01-02T12:00:00Z'),
        closedAt: null,
        dueOn: new DateTime('2023-12-31T23:59:59Z'),
        htmlUrl: 'https://github.com/owner/repo/milestone/1',
        creator: $creator,
    );

    $array = $milestone->toArray();

    expect($array['id'])->toBe(123);
    expect($array['number'])->toBe(1);
    expect($array['title'])->toBe('v1.0');
    expect($array['description'])->toBe('First major release');
    expect($array['state'])->toBe('open');
    expect($array['open_issues'])->toBe(5);
    expect($array['closed_issues'])->toBe(3);
    expect($array['closed_at'])->toBeNull();
    expect($array['due_on'])->toBeString();
    expect($array['creator'])->toBeArray();
    expect($array['creator']['login'])->toBe('creator');
});

test('can check if milestone is open', function () {
    $milestone = new Milestone(
        id: 123,
        number: 1,
        title: 'v1.0',
        description: 'First major release',
        state: 'open',
        openIssues: 5,
        closedIssues: 3,
        createdAt: new DateTime,
        updatedAt: new DateTime,
        closedAt: null,
        dueOn: null,
        htmlUrl: 'https://github.com/owner/repo/milestone/1',
        creator: new User(101, 'creator', 'https://github.com/creator.png', 'https://github.com/creator', 'User'),
    );

    expect($milestone->isOpen())->toBeTrue();
    expect($milestone->isClosed())->toBeFalse();
});

test('can check if milestone is closed', function () {
    $milestone = new Milestone(
        id: 123,
        number: 1,
        title: 'v1.0',
        description: 'First major release',
        state: 'closed',
        openIssues: 0,
        closedIssues: 8,
        createdAt: new DateTime,
        updatedAt: new DateTime,
        closedAt: new DateTime,
        dueOn: null,
        htmlUrl: 'https://github.com/owner/repo/milestone/1',
        creator: new User(101, 'creator', 'https://github.com/creator.png', 'https://github.com/creator', 'User'),
    );

    expect($milestone->isOpen())->toBeFalse();
    expect($milestone->isClosed())->toBeTrue();
});
