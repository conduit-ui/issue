<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Label;
use ConduitUI\Issue\Data\TimelineEvent;
use ConduitUI\Issue\Data\User;

describe('TimelineEvent', function () {
    it('can create timeline event from array', function () {
        $data = [
            'id' => 12345,
            'event' => 'commented',
            'actor' => [
                'id' => 1,
                'login' => 'testuser',
                'avatar_url' => 'https://github.com/testuser.png',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'body' => 'This is a comment',
            'created_at' => '2023-01-01T12:00:00Z',
        ];

        $event = TimelineEvent::fromArray($data);

        expect($event->id)->toBe(12345);
        expect($event->event)->toBe('commented');
        expect($event->actor)->toBeInstanceOf(User::class);
        expect($event->body)->toBe('This is a comment');
    });

    it('can create timeline event with label', function () {
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
            'label' => [
                'id' => 789,
                'name' => 'bug',
                'color' => 'fc2929',
                'description' => 'Something is broken',
            ],
            'created_at' => '2023-01-01T12:00:00Z',
        ];

        $event = TimelineEvent::fromArray($data);

        expect($event->event)->toBe('labeled');
        expect($event->label)->toBeInstanceOf(Label::class);
        expect($event->label->name)->toBe('bug');
    });

    it('can create timeline event with state', function () {
        $data = [
            'id' => 12345,
            'event' => 'closed',
            'actor' => [
                'id' => 1,
                'login' => 'testuser',
                'avatar_url' => 'https://github.com/testuser.png',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'state' => 'closed',
            'state_reason' => 'completed',
            'created_at' => '2023-01-01T12:00:00Z',
        ];

        $event = TimelineEvent::fromArray($data);

        expect($event->event)->toBe('closed');
        expect($event->state)->toBe('closed');
        expect($event->stateReason)->toBe('completed');
    });

    it('can create timeline event with commit', function () {
        $data = [
            'id' => 12345,
            'event' => 'committed',
            'actor' => [
                'id' => 1,
                'login' => 'testuser',
                'avatar_url' => 'https://github.com/testuser.png',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'commit_id' => 'abc123',
            'commit_url' => 'https://github.com/owner/repo/commit/abc123',
            'created_at' => '2023-01-01T12:00:00Z',
        ];

        $event = TimelineEvent::fromArray($data);

        expect($event->event)->toBe('committed');
        expect($event->commitId)->toBe('abc123');
        expect($event->commitUrl)->toBe('https://github.com/owner/repo/commit/abc123');
    });

    it('can create timeline event with cross-reference source', function () {
        $data = [
            'id' => 12345,
            'event' => 'cross-referenced',
            'actor' => [
                'id' => 1,
                'login' => 'testuser',
                'avatar_url' => 'https://github.com/testuser.png',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'source' => [
                'issue' => [
                    'number' => 456,
                ],
            ],
            'created_at' => '2023-01-01T12:00:00Z',
        ];

        $event = TimelineEvent::fromArray($data);

        expect($event->event)->toBe('cross-referenced');
        expect($event->source)->toBeArray();
        expect($event->source['issue']['number'])->toBe(456);
    });

    it('can convert timeline event to array', function () {
        $actor = new User(1, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User');

        $event = new TimelineEvent(
            id: 12345,
            event: 'commented',
            actor: $actor,
            createdAt: new DateTime('2023-01-01T12:00:00Z'),
            body: 'This is a comment',
        );

        $array = $event->toArray();

        expect($array['id'])->toBe(12345);
        expect($array['event'])->toBe('commented');
        expect($array['body'])->toBe('This is a comment');
        expect($array)->toHaveKey('actor');
        expect($array)->not->toHaveKey('commit_id');
    });

    it('can check if timeline event is comment', function () {
        $commentEvent = new TimelineEvent(
            id: 1,
            event: 'commented',
            actor: null,
            createdAt: new DateTime,
        );

        $closedEvent = new TimelineEvent(
            id: 2,
            event: 'closed',
            actor: null,
            createdAt: new DateTime,
        );

        expect($commentEvent->isComment())->toBeTrue();
        expect($closedEvent->isComment())->toBeFalse();
    });

    it('can check if timeline event is cross-reference', function () {
        $crossRefEvent = new TimelineEvent(
            id: 1,
            event: 'cross-referenced',
            actor: null,
            createdAt: new DateTime,
        );

        $commentEvent = new TimelineEvent(
            id: 2,
            event: 'commented',
            actor: null,
            createdAt: new DateTime,
        );

        expect($crossRefEvent->isCrossReference())->toBeTrue();
        expect($commentEvent->isCrossReference())->toBeFalse();
    });

    it('can check if timeline event is commit', function () {
        $commitEvent = new TimelineEvent(
            id: 1,
            event: 'committed',
            actor: null,
            createdAt: new DateTime,
        );

        $commentEvent = new TimelineEvent(
            id: 2,
            event: 'commented',
            actor: null,
            createdAt: new DateTime,
        );

        expect($commitEvent->isCommit())->toBeTrue();
        expect($commentEvent->isCommit())->toBeFalse();
    });

    it('can check if timeline event is review', function () {
        $reviewEvent = new TimelineEvent(
            id: 1,
            event: 'reviewed',
            actor: null,
            createdAt: new DateTime,
        );

        $commentEvent = new TimelineEvent(
            id: 2,
            event: 'commented',
            actor: null,
            createdAt: new DateTime,
        );

        expect($reviewEvent->isReview())->toBeTrue();
        expect($commentEvent->isReview())->toBeFalse();
    });

    it('filters null values from array representation', function () {
        $event = new TimelineEvent(
            id: 12345,
            event: 'closed',
            actor: null,
            createdAt: new DateTime('2023-01-01T12:00:00Z'),
            state: 'closed',
        );

        $array = $event->toArray();

        expect($array)->toHaveKey('id');
        expect($array)->toHaveKey('event');
        expect($array)->toHaveKey('state');
        expect($array)->not->toHaveKey('body');
        expect($array)->not->toHaveKey('commit_id');
    });
});
