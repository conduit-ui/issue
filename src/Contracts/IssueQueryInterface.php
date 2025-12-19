<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Issue;
use Illuminate\Support\Collection;

/**
 * Interface for querying and filtering GitHub issues.
 *
 * Provides a fluent interface for building complex issue queries with
 * filtering, sorting, and pagination capabilities.
 */
interface IssueQueryInterface
{
    /**
     * Filter issues by state (open, closed, or all).
     */
    public function state(string $state): self;

    /**
     * Filter issues by labels.
     *
     * @param  array<string>|string  $labels
     */
    public function labels(array|string $labels): self;

    /**
     * Filter issues by assignee username.
     */
    public function assignee(string $username): self;

    /**
     * Filter issues by creator username.
     */
    public function creator(string $username): self;

    /**
     * Filter issues mentioning a specific user.
     */
    public function mentioned(string $username): self;

    /**
     * Filter issues updated since a given date.
     */
    public function since(string|\DateTimeInterface $date): self;

    /**
     * Sort issues by created, updated, or comments.
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
     * Execute the query and return all matching issues.
     *
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Issue>
     */
    public function get(): Collection;

    /**
     * Execute the query and return the first matching issue.
     */
    public function first(): ?Issue;

    /**
     * Execute the query and return the count of matching issues.
     */
    public function count(): int;
}
