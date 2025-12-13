<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Exceptions;

use Exception;
use Saloon\Http\Response;

class GithubApiException extends Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        public readonly ?Response $response = null,
        public readonly array $context = []
    ) {
        parent::__construct($message, $code);
    }

    public static function fromResponse(Response $response, array $context = []): self
    {
        $body = $response->json();
        $message = is_array($body) && isset($body['message']) && is_string($body['message'])
            ? $body['message']
            : 'Unknown GitHub API error';

        return new self(
            message: $message,
            code: $response->status(),
            response: $response,
            context: $context
        );
    }
}
