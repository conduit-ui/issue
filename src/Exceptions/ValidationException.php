<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Exceptions;

use Saloon\Http\Response;

class ValidationException extends GithubApiException
{
    public function __construct(
        string $message,
        int $code = 422,
        ?Response $response = null,
        array $context = [],
        public readonly array $errors = []
    ) {
        parent::__construct($message, $code, $response, $context);
    }

    public static function fromResponse(Response $response, array $context = []): self
    {
        $body = $response->json();
        $errors = is_array($body) && isset($body['errors']) && is_array($body['errors'])
            ? $body['errors']
            : [];
        $message = is_array($body) && isset($body['message']) && is_string($body['message'])
            ? $body['message']
            : 'Validation failed';

        return new self(
            message: $message,
            code: 422,
            response: $response,
            context: $context,
            errors: $errors
        );
    }
}
