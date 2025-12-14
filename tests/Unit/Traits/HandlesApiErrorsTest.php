<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Exceptions\GithubApiException;
use ConduitUI\Issue\Exceptions\IssueNotFoundException;
use ConduitUI\Issue\Exceptions\RateLimitException;
use ConduitUI\Issue\Exceptions\RepositoryNotFoundException;
use ConduitUI\Issue\Exceptions\ValidationException;
use ConduitUI\Issue\Services\IssuesService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function handlerIssueResponse(): array
{
    return [
        'id' => 1, 'number' => 123, 'title' => 'Issue', 'body' => 'Body', 'state' => 'open',
        'locked' => false, 'comments' => 0,
        'user' => ['id' => 1, 'login' => 'user', 'avatar_url' => 'https://example.com/avatar.png', 'html_url' => 'https://github.com/user', 'type' => 'User'],
        'labels' => [], 'assignees' => [], 'assignee' => null, 'milestone' => null,
        'closed_at' => null, 'closed_by' => null,
        'created_at' => '2024-01-01T00:00:00Z', 'updated_at' => '2024-01-01T00:00:00Z',
        'html_url' => 'https://github.com/owner/repo/issues/123',
    ];
}

describe('HandlesApiErrors', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->service = new IssuesService($this->connector);
    });

    it('handles successful responses', function () {
        $this->mockClient->addResponse(MockResponse::make(handlerIssueResponse()));

        $issue = $this->service->getIssue('owner', 'repo', 123);

        expect($issue->number)->toBe(123);
    });

    it('throws RepositoryNotFoundException for 404 without issue number', function () {
        $this->mockClient->addResponse(MockResponse::make(['message' => 'Not Found'], 404));

        $this->service->listIssues('owner', 'repo');
    })->throws(RepositoryNotFoundException::class, 'Repository not found: owner/repo');

    it('throws IssueNotFoundException for 404 with issue number', function () {
        $this->mockClient->addResponse(MockResponse::make(['message' => 'Not Found'], 404));

        $this->service->getIssue('owner', 'repo', 123);
    })->throws(IssueNotFoundException::class, 'Issue not found: owner/repo#123');

    it('throws ValidationException for 422', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'message' => 'Validation Failed',
            'errors' => [['resource' => 'Issue', 'field' => 'title', 'code' => 'missing']],
        ], 422));

        $this->service->createIssue('owner', 'repo', ['title' => 'Test']);
    })->throws(ValidationException::class, 'Validation Failed');

    it('throws RateLimitException for 429 with zero remaining', function () {
        $this->mockClient->addResponse(MockResponse::make(
            ['message' => 'Rate limit exceeded'],
            429,
            ['X-RateLimit-Remaining' => '0', 'X-RateLimit-Reset' => (string) (time() + 3600)]
        ));

        $this->service->listIssues('owner', 'repo');
    })->throws(RateLimitException::class, 'GitHub API rate limit exceeded');

    it('throws RateLimitException for 403 with zero remaining', function () {
        $this->mockClient->addResponse(MockResponse::make(
            ['message' => 'Rate limit exceeded'],
            403,
            ['X-RateLimit-Remaining' => '0', 'X-RateLimit-Reset' => (string) (time() + 3600)]
        ));

        $this->service->listIssues('owner', 'repo');
    })->throws(RateLimitException::class, 'GitHub API rate limit exceeded');

    it('throws GithubApiException for 403 with remaining quota', function () {
        $this->mockClient->addResponse(MockResponse::make(
            ['message' => 'Forbidden'],
            403,
            ['X-RateLimit-Remaining' => '100']
        ));

        $this->service->listIssues('owner', 'repo');
    })->throws(GithubApiException::class, 'Forbidden');

    it('throws GithubApiException for 429 with remaining quota', function () {
        $this->mockClient->addResponse(MockResponse::make(
            ['message' => 'Too Many Requests'],
            429,
            ['X-RateLimit-Remaining' => '50']
        ));

        $this->service->listIssues('owner', 'repo');
    })->throws(GithubApiException::class, 'Too Many Requests');

    it('throws GithubApiException for other error codes', function () {
        $this->mockClient->addResponse(MockResponse::make(['message' => 'Internal Server Error'], 500));

        $this->service->listIssues('owner', 'repo');
    })->throws(GithubApiException::class, 'Internal Server Error');

    it('uses default message when response has no message', function () {
        $this->mockClient->addResponse(MockResponse::make([], 500));

        try {
            $this->service->listIssues('owner', 'repo');
        } catch (GithubApiException $e) {
            expect($e->getMessage())->toBe('Unknown GitHub API error');
        }
    });

    it('includes issue number in context when provided', function () {
        $this->mockClient->addResponse(MockResponse::make(['message' => 'Error'], 500));

        try {
            $this->service->getIssue('owner', 'repo', 123);
        } catch (GithubApiException $e) {
            expect($e->context)->toHaveKey('issue_number')
                ->and($e->context['issue_number'])->toBe(123);
        }
    });
});
