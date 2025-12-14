<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Data\Reaction;
use Illuminate\Support\Collection;

trait ManagesIssueComments
{
    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Comment>
     */
    public function listComments(string $owner, string $repo, int $issueNumber): Collection
    {
        $response = $this->connector->send(
            $this->connector->get("/repos/{$owner}/{$repo}/issues/{$issueNumber}/comments")
        );

        return collect($response->json())
            ->map(fn (array $data) => Comment::fromArray($data));
    }

    public function getComment(string $owner, string $repo, int $commentId): Comment
    {
        $response = $this->connector->send(
            $this->connector->get("/repos/{$owner}/{$repo}/issues/comments/{$commentId}")
        );

        return Comment::fromArray($response->json());
    }

    public function createComment(string $owner, string $repo, int $issueNumber, string $body): Comment
    {
        $response = $this->connector->send(
            $this->connector->post("/repos/{$owner}/{$repo}/issues/{$issueNumber}/comments", [
                'body' => $body,
            ])
        );

        return Comment::fromArray($response->json());
    }

    public function updateComment(string $owner, string $repo, int $commentId, string $body): Comment
    {
        $response = $this->connector->send(
            $this->connector->patch("/repos/{$owner}/{$repo}/issues/comments/{$commentId}", [
                'body' => $body,
            ])
        );

        return Comment::fromArray($response->json());
    }

    public function deleteComment(string $owner, string $repo, int $commentId): bool
    {
        $response = $this->connector->send(
            $this->connector->delete("/repos/{$owner}/{$repo}/issues/comments/{$commentId}")
        );

        return $response->successful();
    }

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Reaction>
     */
    public function listCommentReactions(string $owner, string $repo, int $commentId): Collection
    {
        $response = $this->connector->send(
            $this->connector->get("/repos/{$owner}/{$repo}/issues/comments/{$commentId}/reactions")
        );

        return collect($response->json())
            ->map(fn (array $data) => Reaction::fromArray($data));
    }

    public function addCommentReaction(string $owner, string $repo, int $commentId, string $content): Reaction
    {
        $response = $this->connector->send(
            $this->connector->post("/repos/{$owner}/{$repo}/issues/comments/{$commentId}/reactions", [
                'content' => $content,
            ])
        );

        return Reaction::fromArray($response->json());
    }

    public function removeCommentReaction(string $owner, string $repo, int $commentId, int $reactionId): bool
    {
        $response = $this->connector->send(
            $this->connector->delete("/repos/{$owner}/{$repo}/issues/comments/{$commentId}/reactions/{$reactionId}")
        );

        return $response->successful();
    }
}
