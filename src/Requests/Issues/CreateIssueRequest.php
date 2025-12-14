<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Issues;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateIssueRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly array $data
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues";
    }

    protected function defaultBody(): array
    {
        return $this->data;
    }
}
