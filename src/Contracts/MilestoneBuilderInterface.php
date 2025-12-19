<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Milestone;

/**
 * Interface for building GitHub milestones.
 *
 * Provides a fluent interface for constructing milestone data
 * before creation in a repository.
 */
interface MilestoneBuilderInterface
{
    /**
     * Set the milestone title.
     */
    public function title(string $title): self;

    /**
     * Set the milestone state (open or closed).
     */
    public function state(string $state): self;

    /**
     * Set the milestone description.
     */
    public function description(string $description): self;

    /**
     * Set the milestone due date.
     */
    public function dueOn(string|\DateTimeInterface $dueOn): self;

    /**
     * Create the milestone and return the Milestone data object.
     */
    public function create(): Milestone;

    /**
     * Get the raw data array without creating the milestone.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
