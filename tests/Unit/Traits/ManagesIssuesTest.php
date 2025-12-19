<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Services\IssuesService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

describe('ManagesIssues', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->service = new IssuesService($this->connector);
    });

    it('lists issues', function () {
        $this->mockClient->addResponse(MockResponse::make([
            fullIssueResponse(['number' => 1, 'title' => 'Issue 1']),
            fullIssueResponse(['number' => 2, 'title' => 'Issue 2', 'state' => 'closed']),
        ]));

        $issues = $this->service->listIssues('owner', 'repo');

        expect($issues)->toHaveCount(2)
            ->and($issues->first())->toBeInstanceOf(Issue::class)
            ->and($issues->first()->title)->toBe('Issue 1');
    });

    it('lists issues with filters', function () {
        $this->mockClient->addResponse(MockResponse::make([
            fullIssueResponse(['number' => 1, 'title' => 'Bug']),
        ]));

        $issues = $this->service->listIssues('owner', 'repo', ['state' => 'open', 'labels' => 'bug']);

        expect($issues)->toHaveCount(1);
    });

    it('gets single issue', function () {
        $this->mockClient->addResponse(MockResponse::make(fullIssueResponse()));

        $issue = $this->service->getIssue('owner', 'repo', 123);

        expect($issue)->toBeInstanceOf(Issue::class)
            ->and($issue->number)->toBe(123)
            ->and($issue->title)->toBe('Test Issue');
    });

    it('creates issue', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullIssueResponse(['number' => 1, 'title' => 'New Issue', 'body' => 'New body']),
            201
        ));

        $issue = $this->service->createIssue('owner', 'repo', ['title' => 'New Issue', 'body' => 'New body']);

        expect($issue)->toBeInstanceOf(Issue::class)
            ->and($issue->title)->toBe('New Issue');
    });

    it('updates issue', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullIssueResponse(['title' => 'Updated Title'])
        ));

        $issue = $this->service->updateIssue('owner', 'repo', 123, ['title' => 'Updated Title']);

        expect($issue->title)->toBe('Updated Title');
    });

    it('closes issue', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullIssueResponse(['state' => 'closed'])
        ));

        $issue = $this->service->closeIssue('owner', 'repo', 123);

        expect($issue->state)->toBe('closed');
    });

    it('reopens issue', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullIssueResponse(['state' => 'open'])
        ));

        $issue = $this->service->reopenIssue('owner', 'repo', 123);

        expect($issue->state)->toBe('open');
    });
});
