<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Data;

readonly class Label
{
    public function __construct(
        public int $id,
        public string $name,
        public string $color,
        public ?string $description,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            color: $data['color'],
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'description' => $this->description,
        ];
    }
}
