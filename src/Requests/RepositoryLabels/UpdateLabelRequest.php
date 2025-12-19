<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\RepositoryLabels;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateLabelRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    public function __construct(
        protected readonly string $fullName,
        protected readonly string $name,
        protected readonly array $attributes,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->fullName}/labels/{$this->name}";
    }

    protected function defaultBody(): array
    {
        return $this->attributes;
    }
}
