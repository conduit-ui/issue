<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Label;
use ConduitUI\Issue\Data\TimelineEvent;
use ConduitUI\Issue\Data\User;

test('can create timeline event from array', function () {
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
        'created_at' => '2023-01-01T12:00:00Z',
        'label' => [
            'id' => 789,
            'name' => 'bug',
            'color' => 'fc2929',
            'description' => 'Something is broken',
        ],
    ];

    $event = TimelineEvent::fromArray($data);

    expect($event->id)->toBe(123);
    expect($event->event)->toBe('labeled');
    expect($event->actor)->toBeInstanceOf(User::class);
    expect($event->actor->login)->toBe('testuser');
    expect($event->label)->toBeInstanceOf(Label::class);
    expect($event->label->name)->toBe('bug');
});

test('can convert timeline event to array', function () {
    $actor = new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User');
    $label = new Label(789, 'bug', 'fc2929', 'Something is broken');

    $event = new TimelineEvent(
        id: 123,
        event: 'labeled',
        actor: $actor,
        createdAt: new DateTime('2023-01-01T12:00:00Z'),
        label: $label,
    );

    $array = $event->toArray();

    expect($array['id'])->toBe(123);
    expect($array['event'])->toBe('labeled');
    expect($array['actor'])->toBeArray();
    expect($array['actor']['login'])->toBe('testuser');
    expect($array['label'])->toBeArray();
    expect($array['label']['name'])->toBe('bug');
});

test('can check if timeline event is comment', function () {
    $event = new TimelineEvent(
        id: 123,
        event: 'commented',
        actor: null,
        createdAt: new DateTime,
    );

    expect($event->isComment())->toBeTrue();
});

test('can check if timeline event is label event', function () {
    $event = new TimelineEvent(
        id: 123,
        event: 'labeled',
        actor: null,
        createdAt: new DateTime,
    );

    expect($event->isLabelEvent())->toBeTrue();
    expect($event->isAssigneeEvent())->toBeFalse();
    expect($event->isMilestoneEvent())->toBeFalse();
    expect($event->isStateEvent())->toBeFalse();
});

test('can check if timeline event is assignee event', function () {
    $event = new TimelineEvent(
        id: 123,
        event: 'assigned',
        actor: null,
        createdAt: new DateTime,
    );

    expect($event->isAssigneeEvent())->toBeTrue();
    expect($event->isLabelEvent())->toBeFalse();
    expect($event->isMilestoneEvent())->toBeFalse();
    expect($event->isStateEvent())->toBeFalse();
});

test('can check if timeline event is milestone event', function () {
    $event = new TimelineEvent(
        id: 123,
        event: 'milestoned',
        actor: null,
        createdAt: new DateTime,
    );

    expect($event->isMilestoneEvent())->toBeTrue();
    expect($event->isLabelEvent())->toBeFalse();
    expect($event->isAssigneeEvent())->toBeFalse();
    expect($event->isStateEvent())->toBeFalse();
});

test('can check if timeline event is state event', function () {
    $event = new TimelineEvent(
        id: 123,
        event: 'closed',
        actor: null,
        createdAt: new DateTime,
    );

    expect($event->isStateEvent())->toBeTrue();
    expect($event->isLabelEvent())->toBeFalse();
    expect($event->isAssigneeEvent())->toBeFalse();
    expect($event->isMilestoneEvent())->toBeFalse();
});

test('can check if timeline event is cross-referenced', function () {
    $event = new TimelineEvent(
        id: 123,
        event: 'cross-referenced',
        actor: null,
        createdAt: new DateTime,
    );

    expect($event->isCrossReferenced())->toBeTrue();
});

test('can create timeline event with comment body', function () {
    $data = [
        'id' => 123,
        'event' => 'commented',
        'created_at' => '2023-01-01T12:00:00Z',
        'updated_at' => '2023-01-02T12:00:00Z',
        'user' => [
            'id' => 456,
            'login' => 'testuser',
            'avatar_url' => 'https://github.com/testuser.png',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'body' => 'This is a comment',
        'author_association' => 'OWNER',
    ];

    $event = TimelineEvent::fromArray($data);

    expect($event->body)->toBe('This is a comment');
    expect($event->user)->toBeInstanceOf(User::class);
    expect($event->updatedAt)->toBeInstanceOf(DateTime::class);
    expect($event->authorAssociation)->toBe('OWNER');
});

test('can create timeline event with state change', function () {
    $data = [
        'id' => 123,
        'event' => 'closed',
        'created_at' => '2023-01-01T12:00:00Z',
        'state' => 'closed',
        'state_reason' => 'completed',
    ];

    $event = TimelineEvent::fromArray($data);

    expect($event->state)->toBe('closed');
    expect($event->stateReason)->toBe('completed');
});

test('can create timeline event with source', function () {
    $data = [
        'id' => 123,
        'event' => 'cross-referenced',
        'created_at' => '2023-01-01T12:00:00Z',
        'source' => [
            'type' => 'issue',
            'issue' => [
                'number' => 456,
            ],
        ],
    ];

    $event = TimelineEvent::fromArray($data);

    expect($event->source)->toBeArray();
    expect($event->source['type'])->toBe('issue');
});
