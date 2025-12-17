<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Reactions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteCommentReactionRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $commentId,
        protected readonly int $reactionId
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/comments/{$this->commentId}/reactions/{$this->reactionId}";
    }
}
