<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Event;
use ConduitUI\Issue\Data\Label;
use ConduitUI\Issue\Data\User;

test('can create event from array', function () {
    $data = [
        'id' => 123,
        'event' => 'labeled',
        'actor' => [
            'id' => 456,
            'login' => 'testuser',
            'avatar_url' => 'https://github.com/testuser.png',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'commit_id' => 'abc123',
        'commit_url' => 'https://github.com/owner/repo/commit/abc123',
        'created_at' => '2023-01-01T12:00:00Z',
        'label' => [
            'id' => 789,
            'name' => 'bug',
            'color' => 'fc2929',
            'description' => 'Something is broken',
        ],
    ];

    $event = Event::fromArray($data);

    expect($event->id)->toBe(123);
    expect($event->event)->toBe('labeled');
    expect($event->actor)->toBeInstanceOf(User::class);
    expect($event->actor->login)->toBe('testuser');
    expect($event->commitId)->toBe('abc123');
    expect($event->commitUrl)->toBe('https://github.com/owner/repo/commit/abc123');
    expect($event->label)->toBeInstanceOf(Label::class);
    expect($event->label->name)->toBe('bug');
});

test('can convert event to array', function () {
    $actor = new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User');
    $label = new Label(789, 'bug', 'fc2929', 'Something is broken');

    $event = new Event(
        id: 123,
        event: 'labeled',
        actor: $actor,
        commitId: 'abc123',
        commitUrl: 'https://github.com/owner/repo/commit/abc123',
        createdAt: new DateTime('2023-01-01T12:00:00Z'),
        label: $label,
    );

    $array = $event->toArray();

    expect($array['id'])->toBe(123);
    expect($array['event'])->toBe('labeled');
    expect($array['actor'])->toBeArray();
    expect($array['actor']['login'])->toBe('testuser');
    expect($array['commit_id'])->toBe('abc123');
    expect($array['label'])->toBeArray();
    expect($array['label']['name'])->toBe('bug');
});

test('can check if event is label event', function () {
    $event = new Event(
        id: 123,
        event: 'labeled',
        actor: null,
        commitId: null,
        commitUrl: null,
        createdAt: new DateTime,
    );

    expect($event->isLabelEvent())->toBeTrue();
    expect($event->isAssigneeEvent())->toBeFalse();
    expect($event->isMilestoneEvent())->toBeFalse();
    expect($event->isStateEvent())->toBeFalse();
});

test('can check if event is assignee event', function () {
    $event = new Event(
        id: 123,
        event: 'assigned',
        actor: null,
        commitId: null,
        commitUrl: null,
        createdAt: new DateTime,
    );

    expect($event->isAssigneeEvent())->toBeTrue();
    expect($event->isLabelEvent())->toBeFalse();
    expect($event->isMilestoneEvent())->toBeFalse();
    expect($event->isStateEvent())->toBeFalse();
});

test('can check if event is milestone event', function () {
    $event = new Event(
        id: 123,
        event: 'milestoned',
        actor: null,
        commitId: null,
        commitUrl: null,
        createdAt: new DateTime,
    );

    expect($event->isMilestoneEvent())->toBeTrue();
    expect($event->isLabelEvent())->toBeFalse();
    expect($event->isAssigneeEvent())->toBeFalse();
    expect($event->isStateEvent())->toBeFalse();
});

test('can check if event is state event', function () {
    $event = new Event(
        id: 123,
        event: 'closed',
        actor: null,
        commitId: null,
        commitUrl: null,
        createdAt: new DateTime,
    );

    expect($event->isStateEvent())->toBeTrue();
    expect($event->isLabelEvent())->toBeFalse();
    expect($event->isAssigneeEvent())->toBeFalse();
    expect($event->isMilestoneEvent())->toBeFalse();
});

test('can create event from array with null actor', function () {
    $data = [
        'id' => 123,
        'event' => 'closed',
        'created_at' => '2023-01-01T12:00:00Z',
    ];

    $event = Event::fromArray($data);

    expect($event->id)->toBe(123);
    expect($event->event)->toBe('closed');
    expect($event->actor)->toBeNull();
    expect($event->commitId)->toBeNull();
    expect($event->label)->toBeNull();
});

test('can create event with assignee', function () {
    $data = [
        'id' => 123,
        'event' => 'assigned',
        'created_at' => '2023-01-01T12:00:00Z',
        'assignee' => [
            'id' => 456,
            'login' => 'assignee',
            'avatar_url' => 'https://github.com/assignee.png',
            'html_url' => 'https://github.com/assignee',
            'type' => 'User',
        ],
    ];

    $event = Event::fromArray($data);

    expect($event->assignee)->toBeInstanceOf(User::class);
    expect($event->assignee->login)->toBe('assignee');
});

test('can create event with milestone', function () {
    $data = [
        'id' => 123,
        'event' => 'milestoned',
        'created_at' => '2023-01-01T12:00:00Z',
        'milestone' => [
            'title' => 'v1.0',
            'number' => 1,
        ],
    ];

    $event = Event::fromArray($data);

    expect($event->milestone)->toBeArray();
    expect($event->milestone['title'])->toBe('v1.0');
});
