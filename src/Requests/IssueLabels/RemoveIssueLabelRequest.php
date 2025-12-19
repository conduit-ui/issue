<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\IssueLabels;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class RemoveIssueLabelRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $fullName,
        protected readonly int $issueNumber,
        protected readonly string $label,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->fullName}/issues/{$this->issueNumber}/labels/{$this->label}";
    }
}
