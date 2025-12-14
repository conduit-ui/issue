<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Milestones;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateMilestoneRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly array $data
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/milestones";
    }

    protected function defaultBody(): array
    {
        return $this->data;
    }
}
