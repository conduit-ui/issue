<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Data\Reaction;
use Illuminate\Support\Collection;

interface ManagesIssueCommentsInterface
{
    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Comment>
     */
    public function listComments(string $owner, string $repo, int $issueNumber): Collection;

    public function getComment(string $owner, string $repo, int $commentId): Comment;

    public function createComment(string $owner, string $repo, int $issueNumber, string $body): Comment;

    public function updateComment(string $owner, string $repo, int $commentId, string $body): Comment;

    public function deleteComment(string $owner, string $repo, int $commentId): bool;

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Reaction>
     */
    public function listCommentReactions(string $owner, string $repo, int $commentId): Collection;

    public function addCommentReaction(string $owner, string $repo, int $commentId, string $content): Reaction;

    public function removeCommentReaction(string $owner, string $repo, int $commentId, int $reactionId): bool;
}
