<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Reactions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListCommentReactionsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $commentId,
        protected readonly array $filters = []
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/comments/{$this->commentId}/reactions";
    }

    protected function defaultQuery(): array
    {
        return $this->filters;
    }
}
