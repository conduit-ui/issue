<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Events;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListIssueTimelineRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $issueNumber,
        protected readonly array $filters = []
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issueNumber}/timeline";
    }

    protected function defaultQuery(): array
    {
        return $this->filters;
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.github.mockingbird-preview+json',
        ];
    }
}
