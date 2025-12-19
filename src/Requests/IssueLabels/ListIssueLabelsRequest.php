<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\IssueLabels;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListIssueLabelsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $fullName,
        protected readonly int $issueNumber,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->fullName}/issues/{$this->issueNumber}/labels";
    }
}
