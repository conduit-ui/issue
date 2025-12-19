<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\Issues;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class LockIssueRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected readonly string $owner,
        protected readonly string $repo,
        protected readonly int $issueNumber,
        protected readonly ?string $lockReason = null
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issueNumber}/lock";
    }

    protected function defaultBody(): array
    {
        return $this->lockReason ? ['lock_reason' => $this->lockReason] : [];
    }
}
