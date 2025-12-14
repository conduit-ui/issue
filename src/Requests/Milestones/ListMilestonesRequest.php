<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Milestones;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListMilestonesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly array $filters = []
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/milestones";
    }

    protected function defaultQuery(): array
    {
        return $this->filters;
    }
}
