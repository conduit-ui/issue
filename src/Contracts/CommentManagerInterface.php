<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Comment;
use Illuminate\Support\Collection;

/**
 * Interface for managing issue comments.
 *
 * Provides operations for creating, updating, deleting,
 * and retrieving comments on GitHub issues.
 */
interface CommentManagerInterface
{
    /**
     * Create a new comment on an issue.
     */
    public function create(int $issueNumber, string $body): Comment;

    /**
     * Update an existing comment.
     */
    public function update(int $commentId, string $body): Comment;

    /**
     * Delete a comment.
     */
    public function delete(int $commentId): bool;

    /**
     * Get all comments for an issue.
     *
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Comment>
     */
    public function list(int $issueNumber): Collection;

    /**
     * Get a specific comment by ID.
     */
    public function get(int $commentId): Comment;
}
