<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Reaction;
use Illuminate\Support\Collection;

interface ManagesCommentReactionsInterface
{
    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Reaction>
     */
    public function listCommentReactions(string $owner, string $repo, int $commentId, array $filters = []): Collection;

    public function createCommentReaction(string $owner, string $repo, int $commentId, string $content): Reaction;

    public function deleteCommentReaction(string $owner, string $repo, int $commentId, int $reactionId): bool;
}
