<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Data\Issue;
use Illuminate\Support\Collection;

/**
 * Interface for managing GitHub issues.
 *
 * Provides high-level operations for CRUD, state management,
 * labels, assignments, and related operations on issues.
 */
interface IssueManagerInterface
{
    /**
     * Find an issue by number.
     */
    public function find(int $issueNumber): Issue;

    /**
     * Create a new query builder for issues.
     */
    public function query(): IssueQueryInterface;

    /**
     * Create a new issue.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Issue;

    /**
     * Update an existing issue.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $issueNumber, array $data): Issue;

    /**
     * Close an issue with an optional comment.
     */
    public function close(int $issueNumber, ?string $comment = null): Issue;

    /**
     * Reopen a closed issue.
     */
    public function reopen(int $issueNumber): Issue;

    /**
     * Lock an issue with an optional reason.
     */
    public function lock(int $issueNumber, ?string $reason = null): bool;

    /**
     * Unlock an issue.
     */
    public function unlock(int $issueNumber): bool;

    /**
     * Add assignees to an issue.
     *
     * @param  array<string>  $assignees
     */
    public function addAssignees(int $issueNumber, array $assignees): Issue;

    /**
     * Remove assignees from an issue.
     *
     * @param  array<string>  $assignees
     */
    public function removeAssignees(int $issueNumber, array $assignees): Issue;

    /**
     * Add labels to an issue.
     *
     * @param  array<string>  $labels
     */
    public function addLabels(int $issueNumber, array $labels): Issue;

    /**
     * Remove labels from an issue.
     *
     * @param  array<string>  $labels
     */
    public function removeLabels(int $issueNumber, array $labels): Issue;

    /**
     * Replace all labels on an issue.
     *
     * @param  array<string>  $labels
     */
    public function replaceLabels(int $issueNumber, array $labels): Issue;

    /**
     * Remove all labels from an issue.
     */
    public function clearLabels(int $issueNumber): Issue;

    /**
     * Get all comments for an issue.
     *
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Comment>
     */
    public function comments(int $issueNumber): Collection;

    /**
     * Add a comment to an issue.
     */
    public function addComment(int $issueNumber, string $body): Comment;
}
