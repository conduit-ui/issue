<?php

declare(strict_types=1);

use ConduitUI\Issue\Exceptions\GithubApiException;
use ConduitUI\Issue\Exceptions\IssueNotFoundException;
use ConduitUI\Issue\Exceptions\RateLimitException;
use ConduitUI\Issue\Exceptions\RepositoryNotFoundException;
use ConduitUI\Issue\Exceptions\ValidationException;

describe('GithubApiException', function () {
    it('stores context', function () {
        $exception = new GithubApiException(
            message: 'Test error',
            code: 500,
            context: ['foo' => 'bar']
        );

        expect($exception->getMessage())->toBe('Test error')
            ->and($exception->getCode())->toBe(500)
            ->and($exception->context)->toBe(['foo' => 'bar']);
    });
});

describe('RepositoryNotFoundException', function () {
    it('creates from repo info', function () {
        $exception = RepositoryNotFoundException::forRepo('owner', 'repo');

        expect($exception->getMessage())->toBe('Repository not found: owner/repo')
            ->and($exception->getCode())->toBe(404)
            ->and($exception->context)->toBe(['owner' => 'owner', 'repo' => 'repo']);
    });
});

describe('IssueNotFoundException', function () {
    it('creates from issue info', function () {
        $exception = IssueNotFoundException::forIssue('owner', 'repo', 123);

        expect($exception->getMessage())->toBe('Issue not found: owner/repo#123')
            ->and($exception->getCode())->toBe(404)
            ->and($exception->context)->toBe([
                'owner' => 'owner',
                'repo' => 'repo',
                'issue_number' => 123,
            ]);
    });
});

describe('RateLimitException', function () {
    it('calculates seconds until reset', function () {
        $resetAt = (new DateTimeImmutable)->modify('+60 seconds');

        $exception = new RateLimitException(
            message: 'Rate limit exceeded',
            resetAt: $resetAt
        );

        $seconds = $exception->getSecondsUntilReset();

        expect($seconds)->toBeGreaterThanOrEqual(59)
            ->and($seconds)->toBeLessThanOrEqual(61);
    });

    it('returns null when no reset time', function () {
        $exception = new RateLimitException('Rate limit exceeded');

        expect($exception->getSecondsUntilReset())->toBeNull();
    });
});

describe('ValidationException', function () {
    it('stores validation errors', function () {
        $errors = [
            ['resource' => 'Issue', 'field' => 'title', 'code' => 'missing'],
        ];

        $exception = new ValidationException(
            message: 'Validation failed',
            errors: $errors
        );

        expect($exception->errors)->toBe($errors);
    });
});
