<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Comment;
use Illuminate\Support\Collection;

interface ManagesIssueCommentsInterface
{
    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Comment>
     */
    public function listComments(string $owner, string $repo, int $issueNumber, array $filters = []): Collection;

    public function getComment(string $owner, string $repo, int $commentId): Comment;

    public function createComment(string $owner, string $repo, int $issueNumber, string $body): Comment;

    public function updateComment(string $owner, string $repo, int $commentId, string $body): Comment;

    public function deleteComment(string $owner, string $repo, int $commentId): bool;
}
