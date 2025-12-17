<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\IssueEvent;
use ConduitUI\Issue\Data\TimelineEvent;
use ConduitUI\Issue\Exceptions\IssueNotFoundException;
use ConduitUI\Issue\Exceptions\RepositoryNotFoundException;
use ConduitUI\Issue\Services\IssuesService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function issueEventResponse(array $overrides = []): array
{
    return array_merge([
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
            'description' => 'Bug label',
        ],
        'created_at' => '2024-01-01T00:00:00Z',
    ], $overrides);
}

function timelineEventResponse(array $overrides = []): array
{
    return array_merge([
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
        'created_at' => '2024-01-01T00:00:00Z',
    ], $overrides);
}

describe('ManagesIssueEvents', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->service = new IssuesService($this->connector);
    });

    describe('listIssueEvents', function () {
        it('lists issue events', function () {
            $this->mockClient->addResponse(MockResponse::make([
                issueEventResponse(['event' => 'labeled']),
                issueEventResponse(['event' => 'assigned', 'label' => null]),
                issueEventResponse(['event' => 'closed', 'label' => null]),
            ]));

            $events = $this->service->listIssueEvents('owner', 'repo', 123);

            expect($events)->toHaveCount(3);
            expect($events->first())->toBeInstanceOf(IssueEvent::class);
            expect($events->first()->event)->toBe('labeled');
            expect($events->get(1)->event)->toBe('assigned');
            expect($events->get(2)->event)->toBe('closed');
        });

        it('lists issue events with filters', function () {
            $this->mockClient->addResponse(MockResponse::make([
                issueEventResponse(['event' => 'labeled']),
            ]));

            $events = $this->service->listIssueEvents('owner', 'repo', 123, ['per_page' => 10]);

            expect($events)->toHaveCount(1);
        });

        it('returns empty collection when no events', function () {
            $this->mockClient->addResponse(MockResponse::make([]));

            $events = $this->service->listIssueEvents('owner', 'repo', 123);

            expect($events)->toBeEmpty();
        });

        it('validates repository', function () {
            expect(fn () => $this->service->listIssueEvents('', 'repo', 123))
                ->toThrow(InvalidArgumentException::class, 'Owner cannot be empty');
        });

        it('validates issue number', function () {
            expect(fn () => $this->service->listIssueEvents('owner', 'repo', 0))
                ->toThrow(InvalidArgumentException::class, 'Issue number must be positive');
        });

        it('handles repository not found', function () {
            $this->mockClient->addResponse(MockResponse::make(
                ['message' => 'Not Found'],
                404
            ));

            expect(fn () => $this->service->listIssueEvents('owner', 'repo', 123))
                ->toThrow(IssueNotFoundException::class);
        });

        it('handles API errors', function () {
            $this->mockClient->addResponse(MockResponse::make(
                ['message' => 'Internal Server Error'],
                500
            ));

            expect(fn () => $this->service->listIssueEvents('owner', 'repo', 123))
                ->toThrow(Exception::class);
        });
    });

    describe('listIssueTimeline', function () {
        it('lists timeline events', function () {
            $this->mockClient->addResponse(MockResponse::make([
                timelineEventResponse(['event' => 'commented']),
                timelineEventResponse(['event' => 'labeled', 'body' => null]),
                timelineEventResponse(['event' => 'closed', 'body' => null]),
            ]));

            $events = $this->service->listIssueTimeline('owner', 'repo', 123);

            expect($events)->toHaveCount(3);
            expect($events->first())->toBeInstanceOf(TimelineEvent::class);
            expect($events->first()->event)->toBe('commented');
            expect($events->get(1)->event)->toBe('labeled');
            expect($events->get(2)->event)->toBe('closed');
        });

        it('lists timeline events with filters', function () {
            $this->mockClient->addResponse(MockResponse::make([
                timelineEventResponse(['event' => 'commented']),
            ]));

            $events = $this->service->listIssueTimeline('owner', 'repo', 123, ['per_page' => 50]);

            expect($events)->toHaveCount(1);
        });

        it('returns empty collection when no timeline events', function () {
            $this->mockClient->addResponse(MockResponse::make([]));

            $events = $this->service->listIssueTimeline('owner', 'repo', 123);

            expect($events)->toBeEmpty();
        });

        it('validates repository', function () {
            expect(fn () => $this->service->listIssueTimeline('', 'repo', 123))
                ->toThrow(InvalidArgumentException::class, 'Owner cannot be empty');
        });

        it('validates issue number', function () {
            expect(fn () => $this->service->listIssueTimeline('owner', 'repo', -1))
                ->toThrow(InvalidArgumentException::class, 'Issue number must be positive');
        });

        it('handles issue not found', function () {
            $this->mockClient->addResponse(MockResponse::make(
                ['message' => 'Not Found'],
                404
            ));

            expect(fn () => $this->service->listIssueTimeline('owner', 'repo', 999))
                ->toThrow(IssueNotFoundException::class);
        });
    });

    describe('getEvent', function () {
        it('gets a single event by id', function () {
            $this->mockClient->addResponse(MockResponse::make(
                issueEventResponse(['id' => 456789, 'event' => 'labeled'])
            ));

            $event = $this->service->getEvent('owner', 'repo', 456789);

            expect($event)->toBeInstanceOf(IssueEvent::class)
                ->and($event->id)->toBe(456789)
                ->and($event->event)->toBe('labeled');
        });

        it('includes actor information', function () {
            $this->mockClient->addResponse(MockResponse::make(
                issueEventResponse(['id' => 123, 'event' => 'assigned', 'label' => null])
            ));

            $event = $this->service->getEvent('owner', 'repo', 123);

            expect($event->actor)->not->toBeNull()
                ->and($event->actor->login)->toBe('testuser');
        });

        it('validates repository', function () {
            expect(fn () => $this->service->getEvent('', 'repo', 123))
                ->toThrow(InvalidArgumentException::class, 'Owner cannot be empty');
        });

        it('validates event id', function () {
            expect(fn () => $this->service->getEvent('owner', 'repo', 0))
                ->toThrow(InvalidArgumentException::class, 'Event ID must be positive');
        });

        it('handles event not found', function () {
            $this->mockClient->addResponse(MockResponse::make(
                ['message' => 'Not Found'],
                404
            ));

            expect(fn () => $this->service->getEvent('owner', 'nonexistent', 123))
                ->toThrow(RepositoryNotFoundException::class);
        });

        it('handles API errors', function () {
            $this->mockClient->addResponse(MockResponse::make(
                ['message' => 'Internal Server Error'],
                500
            ));

            expect(fn () => $this->service->getEvent('owner', 'repo', 123))
                ->toThrow(Exception::class);
        });
    });

    describe('listRepositoryEvents', function () {
        it('lists repository issue events', function () {
            $this->mockClient->addResponse(MockResponse::make([
                issueEventResponse(['event' => 'labeled', 'id' => 1]),
                issueEventResponse(['event' => 'assigned', 'id' => 2, 'label' => null]),
                issueEventResponse(['event' => 'closed', 'id' => 3, 'label' => null]),
            ]));

            $events = $this->service->listRepositoryEvents('owner', 'repo');

            expect($events)->toHaveCount(3);
            expect($events->first())->toBeInstanceOf(IssueEvent::class);
            expect($events->first()->id)->toBe(1);
            expect($events->get(1)->id)->toBe(2);
            expect($events->get(2)->id)->toBe(3);
        });

        it('lists repository events with filters', function () {
            $this->mockClient->addResponse(MockResponse::make([
                issueEventResponse(['event' => 'labeled']),
                issueEventResponse(['event' => 'assigned', 'label' => null]),
            ]));

            $events = $this->service->listRepositoryEvents('owner', 'repo', ['per_page' => 2]);

            expect($events)->toHaveCount(2);
        });

        it('returns empty collection when no repository events', function () {
            $this->mockClient->addResponse(MockResponse::make([]));

            $events = $this->service->listRepositoryEvents('owner', 'repo');

            expect($events)->toBeEmpty();
        });

        it('validates repository', function () {
            expect(fn () => $this->service->listRepositoryEvents('owner', ''))
                ->toThrow(InvalidArgumentException::class, 'Repository name cannot be empty');
        });

        it('handles repository not found', function () {
            $this->mockClient->addResponse(MockResponse::make(
                ['message' => 'Not Found'],
                404
            ));

            expect(fn () => $this->service->listRepositoryEvents('owner', 'nonexistent'))
                ->toThrow(RepositoryNotFoundException::class);
        });

        it('handles API errors', function () {
            $this->mockClient->addResponse(MockResponse::make(
                ['message' => 'Bad Gateway'],
                502
            ));

            expect(fn () => $this->service->listRepositoryEvents('owner', 'repo'))
                ->toThrow(Exception::class);
        });
    });

    describe('Event Types', function () {
        it('handles labeled event', function () {
            $this->mockClient->addResponse(MockResponse::make([
                issueEventResponse([
                    'event' => 'labeled',
                    'label' => [
                        'id' => 1,
                        'name' => 'enhancement',
                        'color' => '84b6eb',
                        'description' => 'New feature',
                    ],
                ]),
            ]));

            $events = $this->service->listIssueEvents('owner', 'repo', 123);

            expect($events->first()->event)->toBe('labeled');
            expect($events->first()->label->name)->toBe('enhancement');
        });

        it('handles assigned event', function () {
            $this->mockClient->addResponse(MockResponse::make([
                issueEventResponse([
                    'event' => 'assigned',
                    'label' => null,
                    'assignee' => [
                        'id' => 2,
                        'login' => 'assignee',
                        'avatar_url' => 'https://github.com/assignee.png',
                        'html_url' => 'https://github.com/assignee',
                        'type' => 'User',
                    ],
                ]),
            ]));

            $events = $this->service->listIssueEvents('owner', 'repo', 123);

            expect($events->first()->event)->toBe('assigned');
            expect($events->first()->assignee->login)->toBe('assignee');
        });

        it('handles milestone event', function () {
            $this->mockClient->addResponse(MockResponse::make([
                issueEventResponse([
                    'event' => 'milestoned',
                    'label' => null,
                    'milestone' => [
                        'title' => 'v2.0',
                    ],
                ]),
            ]));

            $events = $this->service->listIssueEvents('owner', 'repo', 123);

            expect($events->first()->event)->toBe('milestoned');
            expect($events->first()->milestone)->toBe('v2.0');
        });

        it('handles commit reference event', function () {
            $this->mockClient->addResponse(MockResponse::make([
                issueEventResponse([
                    'event' => 'referenced',
                    'label' => null,
                    'commit_id' => 'abc123def456',
                    'commit_url' => 'https://github.com/owner/repo/commit/abc123def456',
                ]),
            ]));

            $events = $this->service->listIssueEvents('owner', 'repo', 123);

            expect($events->first()->event)->toBe('referenced');
            expect($events->first()->commitId)->toBe('abc123def456');
            expect($events->first()->commitUrl)->toBe('https://github.com/owner/repo/commit/abc123def456');
        });
    });

    describe('Timeline Event Types', function () {
        it('handles comment timeline event', function () {
            $this->mockClient->addResponse(MockResponse::make([
                timelineEventResponse([
                    'event' => 'commented',
                    'body' => 'Great work!',
                ]),
            ]));

            $events = $this->service->listIssueTimeline('owner', 'repo', 123);

            expect($events->first()->event)->toBe('commented');
            expect($events->first()->body)->toBe('Great work!');
        });

        it('handles cross-referenced timeline event', function () {
            $this->mockClient->addResponse(MockResponse::make([
                timelineEventResponse([
                    'event' => 'cross-referenced',
                    'body' => null,
                    'source' => [
                        'issue' => [
                            'number' => 456,
                        ],
                    ],
                ]),
            ]));

            $events = $this->service->listIssueTimeline('owner', 'repo', 123);

            expect($events->first()->event)->toBe('cross-referenced');
            expect($events->first()->source)->toBeArray();
        });

        it('handles state change timeline event', function () {
            $this->mockClient->addResponse(MockResponse::make([
                timelineEventResponse([
                    'event' => 'closed',
                    'body' => null,
                    'state' => 'closed',
                    'state_reason' => 'completed',
                ]),
            ]));

            $events = $this->service->listIssueTimeline('owner', 'repo', 123);

            expect($events->first()->event)->toBe('closed');
            expect($events->first()->state)->toBe('closed');
            expect($events->first()->stateReason)->toBe('completed');
        });
    });
});
