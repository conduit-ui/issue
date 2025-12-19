<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\RepositoryLabels;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteLabelRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $fullName,
        protected readonly string $name,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->fullName}/labels/{$this->name}";
    }
}
