<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Comments;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateCommentRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $issueNumber,
        protected readonly string $commentBody
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issueNumber}/comments";
    }

    protected function defaultBody(): array
    {
        return [
            'body' => $this->commentBody,
        ];
    }
}
