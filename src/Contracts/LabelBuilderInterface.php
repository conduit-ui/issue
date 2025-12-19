<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Label;

/**
 * Interface for building GitHub labels.
 *
 * Provides a fluent interface for constructing label data
 * before creation at the repository level.
 */
interface LabelBuilderInterface
{
    /**
     * Set the label name.
     */
    public function name(string $name): self;

    /**
     * Set the label color (hex code without #).
     */
    public function color(string $color): self;

    /**
     * Set the label description.
     */
    public function description(string $description): self;

    /**
     * Create the label and return the Label data object.
     */
    public function create(): Label;

    /**
     * Get the raw data array without creating the label.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
