<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Comments;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCommentRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $commentId
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/comments/{$this->commentId}";
    }
}
