<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Issues;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class UnlockIssueRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $issueNumber
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issueNumber}/lock";
    }
}
