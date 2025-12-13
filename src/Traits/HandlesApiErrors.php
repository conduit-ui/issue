<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Exceptions\GithubApiException;
use ConduitUI\Issue\Exceptions\IssueNotFoundException;
use ConduitUI\Issue\Exceptions\RateLimitException;
use ConduitUI\Issue\Exceptions\RepositoryNotFoundException;
use ConduitUI\Issue\Exceptions\ValidationException;
use Saloon\Http\Response;

trait HandlesApiErrors
{
    /**
     * @throws GithubApiException
     * @throws RepositoryNotFoundException
     * @throws IssueNotFoundException
     * @throws RateLimitException
     * @throws ValidationException
     */
    protected function handleApiResponse(Response $response, string $owner, string $repo, ?int $issueNumber = null): void
    {
        if ($response->successful()) {
            return;
        }

        $context = [
            'owner' => $owner,
            'repo' => $repo,
            'status' => $response->status(),
            'headers' => $response->headers()->all(),
        ];

        if ($issueNumber !== null) {
            $context['issue_number'] = $issueNumber;
        }

        match ($response->status()) {
            404 => $this->handleNotFound($owner, $repo, $issueNumber),
            422 => throw ValidationException::fromResponse($response, $context),
            429, 403 => $this->handleRateLimit($response, $context),
            default => throw GithubApiException::fromResponse($response, $context),
        };
    }

    /**
     * @throws RepositoryNotFoundException
     * @throws IssueNotFoundException
     */
    private function handleNotFound(string $owner, string $repo, ?int $issueNumber): never
    {
        if ($issueNumber !== null) {
            throw IssueNotFoundException::forIssue($owner, $repo, $issueNumber);
        }

        throw RepositoryNotFoundException::forRepo($owner, $repo);
    }

    /**
     * @throws RateLimitException
     * @throws GithubApiException
     */
    private function handleRateLimit(Response $response, array $context): never
    {
        $remaining = $response->headers()->get('X-RateLimit-Remaining');

        if ((is_string($remaining) || is_int($remaining)) && (int) $remaining === 0) {
            throw RateLimitException::fromResponse($response, $context);
        }

        throw GithubApiException::fromResponse($response, $context);
    }
}
