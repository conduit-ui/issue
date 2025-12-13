<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Exceptions;

use DateTimeImmutable;
use Saloon\Http\Response;

class RateLimitException extends GithubApiException
{
    public function __construct(
        string $message,
        int $code = 429,
        ?Response $response = null,
        array $context = [],
        public readonly ?DateTimeImmutable $resetAt = null,
        public readonly ?int $remaining = null,
        public readonly ?int $limit = null
    ) {
        parent::__construct($message, $code, $response, $context);
    }

    public static function fromResponse(Response $response, array $context = []): self
    {
        $headers = $response->headers();
        $resetTimestamp = $headers->get('X-RateLimit-Reset');
        $remaining = $headers->get('X-RateLimit-Remaining');
        $limit = $headers->get('X-RateLimit-Limit');

        $resetAt = is_string($resetTimestamp) || is_int($resetTimestamp)
            ? (new DateTimeImmutable)->setTimestamp((int) $resetTimestamp)
            : null;

        return new self(
            message: 'GitHub API rate limit exceeded',
            code: 429,
            response: $response,
            context: $context,
            resetAt: $resetAt,
            remaining: is_string($remaining) || is_int($remaining) ? (int) $remaining : null,
            limit: is_string($limit) || is_int($limit) ? (int) $limit : null
        );
    }

    public function getSecondsUntilReset(): ?int
    {
        if ($this->resetAt === null) {
            return null;
        }

        return max(0, $this->resetAt->getTimestamp() - time());
    }
}
