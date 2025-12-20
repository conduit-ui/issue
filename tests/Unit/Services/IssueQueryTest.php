<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Services\IssueQuery;
use ConduitUI\Issue\Support\IssueCollection;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

// Helper function is defined in ManagesIssuesTest.php

describe('IssueQuery', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->query = new IssueQuery($this->connector, 'owner', 'repo');
    });

    describe('State Filtering', function () {
        it('filters by open state', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'state' => 'open']),
            ]));

            $issues = $this->query->whereOpen()->get();

            expect($issues)->toHaveCount(1)
                ->and($issues->first())->toBeInstanceOf(Issue::class)
                ->and($issues->first()->state)->toBe('open');
        });

        it('filters by closed state', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'state' => 'closed']),
            ]));

            $issues = $this->query->whereClosed()->get();

            expect($issues)->toHaveCount(1)
                ->and($issues->first()->state)->toBe('closed');
        });

        it('filters by custom state', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'state' => 'open']),
            ]));

            $issues = $this->query->whereState('open')->get();

            expect($issues)->toHaveCount(1);
        });
    });

    describe('Label Filtering', function () {
        it('filters by single label', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'labels' => [['id' => 1, 'name' => 'bug', 'color' => 'ff0000', 'description' => 'Bug']]]),
            ]));

            $issues = $this->query->whereLabel('bug')->get();

            expect($issues)->toHaveCount(1);
        });

        it('filters by multiple labels', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'labels' => [
                    ['id' => 1, 'name' => 'bug', 'color' => 'ff0000', 'description' => 'Bug'],
                    ['id' => 2, 'name' => 'urgent', 'color' => '00ff00', 'description' => 'Urgent'],
                ]]),
            ]));

            $issues = $this->query->whereLabels(['bug', 'urgent'])->get();

            expect($issues)->toHaveCount(1);
        });
    });

    describe('Assignee Filtering', function () {
        it('filters by assignee', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'assignee' => ['id' => 1, 'login' => 'johndoe', 'avatar_url' => 'https://example.com/avatar.png', 'html_url' => 'https://github.com/johndoe', 'type' => 'User']]),
            ]));

            $issues = $this->query->assignedTo('johndoe')->get();

            expect($issues)->toHaveCount(1);
        });

        it('filters unassigned issues', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'assignee' => null]),
            ]));

            $issues = $this->query->whereUnassigned()->get();

            expect($issues)->toHaveCount(1);
        });
    });

    describe('Author Filtering', function () {
        it('filters by creator', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'user' => ['id' => 1, 'login' => 'janedoe', 'avatar_url' => 'https://example.com/avatar.png', 'html_url' => 'https://github.com/janedoe', 'type' => 'User']]),
            ]));

            $issues = $this->query->createdBy('janedoe')->get();

            expect($issues)->toHaveCount(1);
        });

        it('filters by mentioned user', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1]),
            ]));

            $issues = $this->query->mentioning('johndoe')->get();

            expect($issues)->toHaveCount(1);
        });
    });

    describe('Time-Based Filtering', function () {
        it('filters by created after date', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'created_at' => '2024-01-15T00:00:00Z']),
            ]));

            $issues = $this->query->createdAfter('2024-01-01')->get();

            expect($issues)->toHaveCount(1);
        });

        it('filters by updated before date', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'updated_at' => '2024-01-01T00:00:00Z']),
            ]));

            $issues = $this->query->updatedBefore('2024-01-15')->get();

            expect($issues)->toHaveCount(1);
        });

        it('filters old issues by days', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'updated_at' => '2023-01-01T00:00:00Z']),
            ]));

            $issues = $this->query->older(30)->get();

            expect($issues)->toHaveCount(1);
        });
    });

    describe('Sorting', function () {
        it('sorts by created date', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'created_at' => '2024-01-01T00:00:00Z']),
                fullIssueResponse(['number' => 2, 'created_at' => '2024-01-02T00:00:00Z']),
            ]));

            $issues = $this->query->orderByCreated()->get();

            expect($issues)->toHaveCount(2);
        });

        it('sorts by updated date', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'updated_at' => '2024-01-01T00:00:00Z']),
                fullIssueResponse(['number' => 2, 'updated_at' => '2024-01-02T00:00:00Z']),
            ]));

            $issues = $this->query->orderByUpdated()->get();

            expect($issues)->toHaveCount(2);
        });

        it('sorts by custom field and direction', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'comments' => 5]),
                fullIssueResponse(['number' => 2, 'comments' => 10]),
            ]));

            $issues = $this->query->orderBy('comments', 'desc')->get();

            expect($issues)->toHaveCount(2);
        });
    });

    describe('Pagination', function () {
        it('sets per page limit', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1]),
                fullIssueResponse(['number' => 2]),
            ]));

            $issues = $this->query->perPage(2)->get();

            expect($issues)->toHaveCount(2);
        });

        it('sets page number', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 3]),
            ]));

            $issues = $this->query->page(2)->get();

            expect($issues)->toHaveCount(1);
        });
    });

    describe('Method Chaining', function () {
        it('chains multiple filters', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'state' => 'open', 'labels' => [['id' => 1, 'name' => 'bug', 'color' => 'ff0000', 'description' => 'Bug']]]),
            ]));

            $issues = $this->query
                ->whereOpen()
                ->whereLabel('bug')
                ->assignedTo('johndoe')
                ->orderByCreated()
                ->get();

            expect($issues)->toHaveCount(1);
        });

        it('returns self for fluent interface', function () {
            expect($this->query->whereOpen())->toBe($this->query)
                ->and($this->query->whereLabel('bug'))->toBe($this->query)
                ->and($this->query->assignedTo('johndoe'))->toBe($this->query);
        });
    });

    describe('Terminal Methods', function () {
        it('gets all issues', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1]),
                fullIssueResponse(['number' => 2]),
            ]));

            $issues = $this->query->get();

            expect($issues)->toBeInstanceOf(IssueCollection::class)
                ->and($issues)->toHaveCount(2);
        });

        it('gets first issue', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1, 'title' => 'First Issue']),
            ]));

            $issue = $this->query->first();

            expect($issue)->toBeInstanceOf(Issue::class)
                ->and($issue->title)->toBe('First Issue');
        });

        it('returns null when no first issue', function () {
            $this->mockClient->addResponse(MockResponse::make([]));

            $issue = $this->query->first();

            expect($issue)->toBeNull();
        });

        it('counts issues', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1]),
                fullIssueResponse(['number' => 2]),
                fullIssueResponse(['number' => 3]),
            ]));

            $count = $this->query->count();

            expect($count)->toBe(3);
        });

        it('checks if issues exist', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullIssueResponse(['number' => 1]),
            ]));

            expect($this->query->exists())->toBeTrue();
        });

        it('checks if no issues exist', function () {
            $this->mockClient->addResponse(MockResponse::make([]));

            expect($this->query->exists())->toBeFalse();
        });
    });
});
