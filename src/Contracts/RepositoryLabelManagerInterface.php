<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Label;
use Illuminate\Support\Collection;

/**
 * Interface for managing repository-level labels.
 *
 * Provides operations for CRUD of labels at the repository level,
 * allowing management of available labels for issues.
 */
interface RepositoryLabelManagerInterface
{
    /**
     * List all labels in a repository.
     *
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Label>
     */
    public function list(): Collection;

    /**
     * Get a specific label by name.
     */
    public function get(string $name): Label;

    /**
     * Create a new label in the repository.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Label;

    /**
     * Update an existing label.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(string $name, array $data): Label;

    /**
     * Delete a label from the repository.
     */
    public function delete(string $name): bool;
}
