<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Services\IssuesService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function assigneeIssueResponse(array $assignees = []): array
{
    return [
        'id' => 1,
        'number' => 123,
        'title' => 'Issue',
        'body' => 'Body',
        'state' => 'open',
        'locked' => false,
        'comments' => 0,
        'user' => ['id' => 1, 'login' => 'user', 'avatar_url' => 'https://example.com/avatar.png', 'html_url' => 'https://github.com/user', 'type' => 'User'],
        'labels' => [],
        'assignees' => $assignees,
        'assignee' => null,
        'milestone' => null,
        'closed_at' => null,
        'closed_by' => null,
        'created_at' => '2024-01-01T00:00:00Z',
        'updated_at' => '2024-01-01T00:00:00Z',
        'html_url' => 'https://github.com/owner/repo/issues/123',
    ];
}

describe('ManagesIssueAssignees', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->service = new IssuesService($this->connector);
    });

    it('adds assignees', function () {
        $this->mockClient->addResponse(MockResponse::make(assigneeIssueResponse([
            ['id' => 1, 'login' => 'user1', 'avatar_url' => 'https://example.com/avatar.png', 'html_url' => 'https://github.com/user1', 'type' => 'User'],
            ['id' => 2, 'login' => 'user2', 'avatar_url' => 'https://example.com/avatar.png', 'html_url' => 'https://github.com/user2', 'type' => 'User'],
        ])));

        $issue = $this->service->addAssignees('owner', 'repo', 123, ['user1', 'user2']);

        expect($issue)->toBeInstanceOf(Issue::class)
            ->and($issue->assignees)->toHaveCount(2);
    });

    it('removes assignees', function () {
        $this->mockClient->addResponse(MockResponse::make(assigneeIssueResponse()));

        $issue = $this->service->removeAssignees('owner', 'repo', 123, ['user1']);

        expect($issue)->toBeInstanceOf(Issue::class)
            ->and($issue->assignees)->toHaveCount(0);
    });

    it('assigns single user', function () {
        $this->mockClient->addResponse(MockResponse::make(assigneeIssueResponse([
            ['id' => 1, 'login' => 'user1', 'avatar_url' => 'https://example.com/avatar.png', 'html_url' => 'https://github.com/user1', 'type' => 'User'],
        ])));

        $issue = $this->service->assignIssue('owner', 'repo', 123, 'user1');

        expect($issue)->toBeInstanceOf(Issue::class);
    });

    it('unassigns single user', function () {
        $this->mockClient->addResponse(MockResponse::make(assigneeIssueResponse()));

        $issue = $this->service->unassignIssue('owner', 'repo', 123, 'user1');

        expect($issue)->toBeInstanceOf(Issue::class);
    });
});
