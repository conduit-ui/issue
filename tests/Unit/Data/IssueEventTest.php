<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\IssueEvent;
use ConduitUI\Issue\Data\Label;
use ConduitUI\Issue\Data\User;

test('can create issue event from array', function () {
    $data = [
        'id' => 12345,
        'event' => 'labeled',
        'actor' => [
            'id' => 1,
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

    $event = IssueEvent::fromArray($data);

    expect($event->id)->toBe(12345);
    expect($event->event)->toBe('labeled');
    expect($event->actor)->toBeInstanceOf(User::class);
    expect($event->actor->login)->toBe('testuser');
    expect($event->label)->toBeInstanceOf(Label::class);
    expect($event->label->name)->toBe('bug');
    expect($event->createdAt)->toBeInstanceOf(DateTime::class);
});

test('can create issue event with null actor', function () {
    $data = [
        'id' => 12345,
        'event' => 'closed',
        'actor' => null,
        'created_at' => '2023-01-01T12:00:00Z',
    ];

    $event = IssueEvent::fromArray($data);

    expect($event->id)->toBe(12345);
    expect($event->event)->toBe('closed');
    expect($event->actor)->toBeNull();
});

test('can create assigned event', function () {
    $data = [
        'id' => 12345,
        'event' => 'assigned',
        'actor' => [
            'id' => 1,
            'login' => 'assigner',
            'avatar_url' => 'https://github.com/assigner.png',
            'html_url' => 'https://github.com/assigner',
            'type' => 'User',
        ],
        'assignee' => [
            'id' => 2,
            'login' => 'assignee',
            'avatar_url' => 'https://github.com/assignee.png',
            'html_url' => 'https://github.com/assignee',
            'type' => 'User',
        ],
        'created_at' => '2023-01-01T12:00:00Z',
    ];

    $event = IssueEvent::fromArray($data);

    expect($event->event)->toBe('assigned');
    expect($event->assignee)->toBeInstanceOf(User::class);
    expect($event->assignee->login)->toBe('assignee');
});

test('can create milestone event', function () {
    $data = [
        'id' => 12345,
        'event' => 'milestoned',
        'actor' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://github.com/testuser.png',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'milestone' => [
            'title' => 'v1.0',
        ],
        'created_at' => '2023-01-01T12:00:00Z',
    ];

    $event = IssueEvent::fromArray($data);

    expect($event->event)->toBe('milestoned');
    expect($event->milestone)->toBe('v1.0');
});

test('can create commit event', function () {
    $data = [
        'id' => 12345,
        'event' => 'referenced',
        'actor' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://github.com/testuser.png',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'commit_id' => 'abc123def456',
        'commit_url' => 'https://github.com/owner/repo/commit/abc123def456',
        'created_at' => '2023-01-01T12:00:00Z',
    ];

    $event = IssueEvent::fromArray($data);

    expect($event->event)->toBe('referenced');
    expect($event->commitId)->toBe('abc123def456');
    expect($event->commitUrl)->toBe('https://github.com/owner/repo/commit/abc123def456');
});

test('can convert issue event to array', function () {
    $actor = new User(1, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User');
    $label = new Label(789, 'bug', 'fc2929', 'Something is broken');

    $event = new IssueEvent(
        id: 12345,
        event: 'labeled',
        actor: $actor,
        createdAt: new DateTime('2023-01-01T12:00:00Z'),
        label: $label,
    );

    $array = $event->toArray();

    expect($array['id'])->toBe(12345);
    expect($array['event'])->toBe('labeled');
    expect($array['actor'])->toBeArray();
    expect($array['actor']['login'])->toBe('testuser');
    expect($array['label'])->toBeArray();
    expect($array['label']['name'])->toBe('bug');
    expect($array['created_at'])->toBeString();
});

test('can check if event is label event', function () {
    $labeledEvent = new IssueEvent(
        id: 1,
        event: 'labeled',
        actor: null,
        createdAt: new DateTime,
    );

    $unlabeledEvent = new IssueEvent(
        id: 2,
        event: 'unlabeled',
        actor: null,
        createdAt: new DateTime,
    );

    $closedEvent = new IssueEvent(
        id: 3,
        event: 'closed',
        actor: null,
        createdAt: new DateTime,
    );

    expect($labeledEvent->isLabelEvent())->toBeTrue();
    expect($unlabeledEvent->isLabelEvent())->toBeTrue();
    expect($closedEvent->isLabelEvent())->toBeFalse();
});

test('can check if event is assignee event', function () {
    $assignedEvent = new IssueEvent(
        id: 1,
        event: 'assigned',
        actor: null,
        createdAt: new DateTime,
    );

    $unassignedEvent = new IssueEvent(
        id: 2,
        event: 'unassigned',
        actor: null,
        createdAt: new DateTime,
    );

    $closedEvent = new IssueEvent(
        id: 3,
        event: 'closed',
        actor: null,
        createdAt: new DateTime,
    );

    expect($assignedEvent->isAssigneeEvent())->toBeTrue();
    expect($unassignedEvent->isAssigneeEvent())->toBeTrue();
    expect($closedEvent->isAssigneeEvent())->toBeFalse();
});

test('can check if event is state event', function () {
    $closedEvent = new IssueEvent(
        id: 1,
        event: 'closed',
        actor: null,
        createdAt: new DateTime,
    );

    $reopenedEvent = new IssueEvent(
        id: 2,
        event: 'reopened',
        actor: null,
        createdAt: new DateTime,
    );

    $labeledEvent = new IssueEvent(
        id: 3,
        event: 'labeled',
        actor: null,
        createdAt: new DateTime,
    );

    expect($closedEvent->isStateEvent())->toBeTrue();
    expect($reopenedEvent->isStateEvent())->toBeTrue();
    expect($labeledEvent->isStateEvent())->toBeFalse();
});

test('can check if event is milestone event', function () {
    $milestonedEvent = new IssueEvent(
        id: 1,
        event: 'milestoned',
        actor: null,
        createdAt: new DateTime,
    );

    $demilestonedEvent = new IssueEvent(
        id: 2,
        event: 'demilestoned',
        actor: null,
        createdAt: new DateTime,
    );

    $closedEvent = new IssueEvent(
        id: 3,
        event: 'closed',
        actor: null,
        createdAt: new DateTime,
    );

    expect($milestonedEvent->isMilestoneEvent())->toBeTrue();
    expect($demilestonedEvent->isMilestoneEvent())->toBeTrue();
    expect($closedEvent->isMilestoneEvent())->toBeFalse();
});
