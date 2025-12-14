<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Comments;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListCommentsRequest extends Request
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
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issueNumber}/comments";
    }

    protected function defaultQuery(): array
    {
        return $this->filters;
    }
}
