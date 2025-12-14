<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Milestones;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateMilestoneRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $milestoneNumber,
        protected readonly array $data
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/milestones/{$this->milestoneNumber}";
    }

    protected function defaultBody(): array
    {
        return $this->data;
    }
}
