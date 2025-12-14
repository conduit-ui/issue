<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Labels;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class RemoveLabelRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $issueNumber,
        protected readonly string $label
    ) {}

    public function resolveEndpoint(): string
    {
        $encodedLabel = rawurlencode($this->label);

        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issueNumber}/labels/{$encodedLabel}";
    }
}
