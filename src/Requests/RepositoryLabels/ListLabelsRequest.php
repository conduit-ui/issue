<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\RepositoryLabels;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListLabelsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $fullName,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->fullName}/labels";
    }
}
