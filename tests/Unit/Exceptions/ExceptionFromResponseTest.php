<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Exceptions\GithubApiException;
use ConduitUI\Issue\Exceptions\RateLimitException;
use ConduitUI\Issue\Exceptions\ValidationException;
use ConduitUI\Issue\Services\IssuesService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

describe('GithubApiException::fromResponse', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->service = new IssuesService($this->connector);
    });

    it('extracts message from response body', function () {
        $this->mockClient->addResponse(MockResponse::make(['message' => 'Server Error'], 500));

        try {
            $this->service->listIssues('owner', 'repo');
        } catch (GithubApiException $e) {
            expect($e->getMessage())->toBe('Server Error')
                ->and($e->getCode())->toBe(500)
                ->and($e->response)->not->toBeNull()
                ->and($e->context)->toHaveKey('owner');
        }
    });

    it('uses default message when body has no message', function () {
        $this->mockClient->addResponse(MockResponse::make([], 500));

        try {
            $this->service->listIssues('owner', 'repo');
        } catch (GithubApiException $e) {
            expect($e->getMessage())->toBe('Unknown GitHub API error');
        }
    });

    it('uses default message when message is not a string', function () {
        $this->mockClient->addResponse(MockResponse::make(['message' => 123], 500));

        try {
            $this->service->listIssues('owner', 'repo');
        } catch (GithubApiException $e) {
            expect($e->getMessage())->toBe('Unknown GitHub API error');
        }
    });
});

describe('RateLimitException::fromResponse', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->service = new IssuesService($this->connector);
    });

    it('extracts rate limit headers', function () {
        $resetTime = time() + 3600;
        $this->mockClient->addResponse(MockResponse::make(
            ['message' => 'Rate limit exceeded'],
            429,
            [
                'X-RateLimit-Reset' => (string) $resetTime,
                'X-RateLimit-Remaining' => '0',
                'X-RateLimit-Limit' => '5000',
            ]
        ));

        try {
            $this->service->listIssues('owner', 'repo');
        } catch (RateLimitException $e) {
            expect($e->getMessage())->toBe('GitHub API rate limit exceeded')
                ->and($e->getCode())->toBe(429)
                ->and($e->remaining)->toBe(0)
                ->and($e->limit)->toBe(5000)
                ->and($e->resetAt)->not->toBeNull()
                ->and($e->context)->toHaveKey('owner');
        }
    });

    it('handles missing headers gracefully', function () {
        $this->mockClient->addResponse(MockResponse::make(
            [],
            429,
            ['X-RateLimit-Remaining' => '0']
        ));

        try {
            $this->service->listIssues('owner', 'repo');
        } catch (RateLimitException $e) {
            expect($e->resetAt)->toBeNull()
                ->and($e->limit)->toBeNull();
        }
    });
});

describe('ValidationException::fromResponse', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->service = new IssuesService($this->connector);
    });

    it('extracts errors from response body', function () {
        $errors = [
            ['resource' => 'Issue', 'field' => 'title', 'code' => 'missing'],
        ];
        $this->mockClient->addResponse(MockResponse::make([
            'message' => 'Validation Failed',
            'errors' => $errors,
        ], 422));

        try {
            $this->service->createIssue('owner', 'repo', ['title' => 'Test']);
        } catch (ValidationException $e) {
            expect($e->getMessage())->toBe('Validation Failed')
                ->and($e->getCode())->toBe(422)
                ->and($e->errors)->toBe($errors)
                ->and($e->context)->toHaveKey('owner');
        }
    });

    it('uses default message when body has no message', function () {
        $this->mockClient->addResponse(MockResponse::make([], 422));

        try {
            $this->service->createIssue('owner', 'repo', ['title' => 'Test']);
        } catch (ValidationException $e) {
            expect($e->getMessage())->toBe('Validation failed');
        }
    });

    it('handles missing errors array', function () {
        $this->mockClient->addResponse(MockResponse::make(['message' => 'Failed'], 422));

        try {
            $this->service->createIssue('owner', 'repo', ['title' => 'Test']);
        } catch (ValidationException $e) {
            expect($e->errors)->toBe([]);
        }
    });

    it('handles non-array errors', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'message' => 'Failed',
            'errors' => 'not an array',
        ], 422));

        try {
            $this->service->createIssue('owner', 'repo', ['title' => 'Test']);
        } catch (ValidationException $e) {
            expect($e->errors)->toBe([]);
        }
    });
});
