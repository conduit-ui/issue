<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Requests\RepositoryLabels;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateLabelRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $fullName,
        protected readonly string $name,
        protected readonly string $color,
        protected readonly ?string $description = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->fullName}/labels";
    }

    protected function defaultBody(): array
    {
        return [
            'name' => $this->name,
            'color' => $this->color,
            'description' => $this->description,
        ];
    }
}
