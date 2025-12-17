<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Events;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetEventRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $eventId
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/events/{$this->eventId}";
    }
}
