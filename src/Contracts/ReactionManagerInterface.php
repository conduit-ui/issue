<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Reaction;
use Illuminate\Support\Collection;

/**
 * Interface for managing comment reactions.
 *
 * Provides operations for creating, deleting, and retrieving
 * emoji reactions on issue comments, with convenient shortcuts
 * for common reaction types.
 */
interface ReactionManagerInterface
{
    /**
     * Create a reaction on a comment.
     */
    public function create(int $commentId, string $content): Reaction;

    /**
     * Delete a reaction from a comment.
     */
    public function delete(int $commentId, int $reactionId): bool;

    /**
     * List all reactions on a comment.
     *
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Reaction>
     */
    public function list(int $commentId, array $filters = []): Collection;

    /**
     * Add a thumbs up reaction.
     */
    public function thumbsUp(int $commentId): Reaction;

    /**
     * Add a thumbs down reaction.
     */
    public function thumbsDown(int $commentId): Reaction;

    /**
     * Add a laugh reaction.
     */
    public function laugh(int $commentId): Reaction;

    /**
     * Add a hooray reaction.
     */
    public function hooray(int $commentId): Reaction;

    /**
     * Add a confused reaction.
     */
    public function confused(int $commentId): Reaction;

    /**
     * Add a heart reaction.
     */
    public function heart(int $commentId): Reaction;

    /**
     * Add a rocket reaction.
     */
    public function rocket(int $commentId): Reaction;

    /**
     * Add an eyes reaction.
     */
    public function eyes(int $commentId): Reaction;
}
