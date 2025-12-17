<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Reactions;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateCommentReactionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $commentId,
        protected readonly string $content
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/comments/{$this->commentId}/reactions";
    }

    protected function defaultBody(): array
    {
        return [
            'content' => $this->content,
        ];
    }
}
