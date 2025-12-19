<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Milestone;
use Illuminate\Support\Collection;

/**
 * Interface for querying and filtering GitHub milestones.
 *
 * Provides a fluent interface for building milestone queries with
 * filtering, sorting, and pagination capabilities.
 */
interface MilestoneQueryInterface
{
    /**
     * Filter milestones by state (open, closed, or all).
     */
    public function state(string $state): self;

    /**
     * Sort milestones by due_on or completeness.
     */
    public function sort(string $field): self;

    /**
     * Set sort direction (asc or desc).
     */
    public function direction(string $direction): self;

    /**
     * Set the number of results per page.
     */
    public function perPage(int $perPage): self;

    /**
     * Set the page number.
     */
    public function page(int $page): self;

    /**
     * Execute the query and return all matching milestones.
     *
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Milestone>
     */
    public function get(): Collection;

    /**
     * Execute the query and return the first matching milestone.
     */
    public function first(): ?Milestone;

    /**
     * Execute the query and return the count of matching milestones.
     */
    public function count(): int;
}
