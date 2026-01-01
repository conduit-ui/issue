<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\IssueLabels;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class SetIssueLabelsRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected readonly string $fullName,
        protected readonly int $issueNumber,
        protected readonly array $labels,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->fullName}/issues/{$this->issueNumber}/labels";
    }

    protected function defaultBody(): array
    {
        return ['labels' => $this->labels];
    }
}
