<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Services\IssuesService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function labelIssueResponse(array $labels = []): array
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
        'labels' => $labels,
        'assignees' => [],
        'assignee' => null,
        'milestone' => null,
        'closed_at' => null,
        'closed_by' => null,
        'created_at' => '2024-01-01T00:00:00Z',
        'updated_at' => '2024-01-01T00:00:00Z',
        'html_url' => 'https://github.com/owner/repo/issues/123',
    ];
}

describe('ManagesIssueLabels', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->service = new IssuesService($this->connector);
    });

    it('adds labels', function () {
        $this->mockClient->addResponse(MockResponse::make(labelIssueResponse([
            ['id' => 1, 'name' => 'bug', 'color' => 'ff0000', 'description' => 'Bug label'],
            ['id' => 2, 'name' => 'urgent', 'color' => 'ff0000', 'description' => 'Urgent label'],
        ])));

        $issue = $this->service->addLabels('owner', 'repo', 123, ['bug', 'urgent']);

        expect($issue)->toBeInstanceOf(Issue::class)
            ->and($issue->labels)->toHaveCount(2);
    });

    it('adds single label', function () {
        $this->mockClient->addResponse(MockResponse::make(labelIssueResponse([
            ['id' => 1, 'name' => 'bug', 'color' => 'ff0000', 'description' => 'Bug label'],
        ])));

        $issue = $this->service->addLabel('owner', 'repo', 123, 'bug');

        expect($issue)->toBeInstanceOf(Issue::class);
    });

    it('removes labels', function () {
        // First response for removing label, second for getIssue
        $this->mockClient->addResponse(MockResponse::make([], 204));
        $this->mockClient->addResponse(MockResponse::make(labelIssueResponse()));

        $issue = $this->service->removeLabels('owner', 'repo', 123, ['bug']);

        expect($issue)->toBeInstanceOf(Issue::class);
    });

    it('removes single label', function () {
        $this->mockClient->addResponse(MockResponse::make([], 204));
        $this->mockClient->addResponse(MockResponse::make(labelIssueResponse()));

        $issue = $this->service->removeLabel('owner', 'repo', 123, 'bug');

        expect($issue)->toBeInstanceOf(Issue::class);
    });

    it('replaces all labels', function () {
        $this->mockClient->addResponse(MockResponse::make(labelIssueResponse([
            ['id' => 1, 'name' => 'feature', 'color' => '00ff00', 'description' => 'Feature label'],
        ])));

        $issue = $this->service->replaceAllLabels('owner', 'repo', 123, ['feature']);

        expect($issue)->toBeInstanceOf(Issue::class);
    });

    it('removes all labels', function () {
        $this->mockClient->addResponse(MockResponse::make([], 204));
        $this->mockClient->addResponse(MockResponse::make(labelIssueResponse()));

        $issue = $this->service->removeAllLabels('owner', 'repo', 123);

        expect($issue)->toBeInstanceOf(Issue::class)
            ->and($issue->labels)->toHaveCount(0);
    });
});
