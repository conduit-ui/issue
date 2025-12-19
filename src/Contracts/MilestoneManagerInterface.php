<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Milestone;

/**
 * Interface for managing GitHub milestones.
 *
 * Provides high-level operations for CRUD and state management
 * of milestones in a repository.
 */
interface MilestoneManagerInterface
{
    /**
     * Find a milestone by number.
     */
    public function find(int $milestoneNumber): Milestone;

    /**
     * Create a new query builder for milestones.
     */
    public function query(): MilestoneQueryInterface;

    /**
     * Create a new milestone.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Milestone;

    /**
     * Update an existing milestone.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $milestoneNumber, array $data): Milestone;

    /**
     * Delete a milestone.
     */
    public function delete(int $milestoneNumber): bool;

    /**
     * Close a milestone.
     */
    public function close(int $milestoneNumber): Milestone;

    /**
     * Reopen a closed milestone.
     */
    public function reopen(int $milestoneNumber): Milestone;
}
