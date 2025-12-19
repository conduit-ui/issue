<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Services\TimelineQuery;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function timelineEventForQuery(string $event, array $overrides = []): array
{
    return array_merge([
        'id' => rand(1, 10000),
        'event' => $event,
        'actor' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.png',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'created_at' => '2024-01-01T00:00:00Z',
    ], $overrides);
}

describe('TimelineQuery', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->query = new TimelineQuery($this->connector, 'owner/repo', 123);
    });

    describe('get', function () {
        it('gets all timeline events for an issue', function () {
            $this->mockClient->addResponse(MockResponse::make([
                timelineEventForQuery('commented', ['body' => 'First comment']),
                timelineEventForQuery('labeled', ['label' => ['name' => 'bug']]),
                timelineEventForQuery('closed'),
            ]));

            $events = $this->query->get();

            expect($events)->toHaveCount(3)
                ->and($events[0]['event'])->toBe('commented')
                ->and($events[1]['event'])->toBe('labeled')
                ->and($events[2]['event'])->toBe('closed');
        });

        it('returns empty collection when no events exist', function () {
            $this->mockClient->addResponse(MockResponse::make([]));

            $events = $this->query->get();

            expect($events)->toBeEmpty();
        });
    });

    describe('ofType', function () {
        beforeEach(function () {
            $this->mockClient->addResponse(MockResponse::make([
                timelineEventForQuery('commented', ['body' => 'Comment 1']),
                timelineEventForQuery('labeled', ['label' => ['name' => 'bug']]),
                timelineEventForQuery('commented', ['body' => 'Comment 2']),
                timelineEventForQuery('closed'),
                timelineEventForQuery('unlabeled', ['label' => ['name' => 'bug']]),
            ]));
        });

        it('filters events by a single type', function () {
            $events = $this->query->ofType('commented');

            expect($events)->toHaveCount(2)
                ->and($events->first()['event'])->toBe('commented')
                ->and($events->last()['event'])->toBe('commented');
        });

        it('filters events by multiple types', function () {
            $events = $this->query->ofType(['labeled', 'unlabeled']);

            expect($events)->toHaveCount(2)
                ->and($events->first()['event'])->toBe('labeled')
                ->and($events->last()['event'])->toBe('unlabeled');
        });

        it('returns empty collection when no matching events exist', function () {
            $events = $this->query->ofType('renamed');

            expect($events)->toBeEmpty();
        });
    });

    describe('comments', function () {
        it('gets only comment events', function () {
            $this->mockClient->addResponse(MockResponse::make([
                timelineEventForQuery('commented', ['body' => 'Comment 1']),
                timelineEventForQuery('labeled'),
                timelineEventForQuery('commented', ['body' => 'Comment 2']),
                timelineEventForQuery('closed'),
            ]));

            $comments = $this->query->comments();

            expect($comments)->toHaveCount(2)
                ->and($comments->first()['event'])->toBe('commented')
                ->and($comments->last()['event'])->toBe('commented');
        });
    });

    describe('labels', function () {
        it('gets only label-related events', function () {
            $this->mockClient->addResponse(MockResponse::make([
                timelineEventForQuery('commented'),
                timelineEventForQuery('labeled', ['label' => ['name' => 'bug']]),
                timelineEventForQuery('unlabeled', ['label' => ['name' => 'wontfix']]),
                timelineEventForQuery('closed'),
                timelineEventForQuery('labeled', ['label' => ['name' => 'enhancement']]),
            ]));

            $labelEvents = $this->query->labels();

            expect($labelEvents)->toHaveCount(3)
                ->and($labelEvents->first()['event'])->toBe('labeled')
                ->and($labelEvents->last()['event'])->toBe('labeled');
        });
    });
});
