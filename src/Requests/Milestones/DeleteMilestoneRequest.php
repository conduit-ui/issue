<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Milestones;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteMilestoneRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $milestoneNumber
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/milestones/{$this->milestoneNumber}";
    }
}
