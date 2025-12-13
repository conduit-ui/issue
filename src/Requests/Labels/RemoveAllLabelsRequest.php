<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Labels;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class RemoveAllLabelsRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $issueNumber
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issueNumber}/labels";
    }
}
