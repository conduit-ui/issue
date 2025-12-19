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
        public bool $default = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            color: $data['color'],
            description: $data['description'] ?? null,
            default: $data['default'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'description' => $this->description,
            'default' => $this->default,
        ];
    }

    /**
     * Get full hex color with #
     */
    public function hexColor(): string
    {
        return '#'.$this->color;
    }

    /**
     * Check if color is light or dark
     */
    public function isLightColor(): bool
    {
        $r = hexdec(substr($this->color, 0, 2));
        $g = hexdec(substr($this->color, 2, 2));
        $b = hexdec(substr($this->color, 4, 2));

        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return $brightness > 155;
    }
}
