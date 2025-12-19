<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Data\Reaction;
use ConduitUI\Issue\Requests\Comments\DeleteCommentRequest;
use ConduitUI\Issue\Requests\Comments\GetCommentRequest;
use ConduitUI\Issue\Requests\Comments\UpdateCommentRequest;
use ConduitUI\Issue\Requests\Reactions\CreateCommentReactionRequest;
use ConduitUI\Issue\Requests\Reactions\DeleteCommentReactionRequest;
use ConduitUI\Issue\Requests\Reactions\ListCommentReactionsRequest;
use Illuminate\Support\Collection;

final class CommentInstance
{
    protected ?Comment $comment = null;

    public function __construct(
        protected readonly Connector $connector,
        protected readonly string $fullName,
        protected readonly int $commentId,
    ) {}

    /**
     * Get the comment data.
     */
    public function get(): Comment
    {
        if ($this->comment === null) {
            $this->comment = $this->fetch();
        }

        return $this->comment;
    }

    /**
     * Update comment body.
     */
    public function update(string $body): self
    {
        [$owner, $repo] = explode('/', $this->fullName);

        $response = $this->connector->send(
            new UpdateCommentRequest($owner, $repo, $this->commentId, $body)
        );

        $this->comment = Comment::fromArray($response->json());

        return $this;
    }

    /**
     * Delete the comment.
     */
    public function delete(): bool
    {
        [$owner, $repo] = explode('/', $this->fullName);

        $response = $this->connector->send(
            new DeleteCommentRequest($owner, $repo, $this->commentId)
        );

        return $response->status() === 204;
    }

    /**
     * Add a reaction to the comment.
     */
    public function react(string $content): Reaction
    {
        [$owner, $repo] = explode('/', $this->fullName);

        $response = $this->connector->send(
            new CreateCommentReactionRequest($owner, $repo, $this->commentId, $content)
        );

        return Reaction::fromArray($response->json());
    }

    /**
     * Get all reactions on the comment.
     *
     * @return \Illuminate\Support\Collection<int, Reaction>
     */
    public function reactions(): Collection
    {
        [$owner, $repo] = explode('/', $this->fullName);

        $response = $this->connector->send(
            new ListCommentReactionsRequest($owner, $repo, $this->commentId)
        );

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return collect($items)
            ->map(fn (array $reaction): Reaction => Reaction::fromArray($reaction));
    }

    /**
     * Delete a reaction.
     */
    public function deleteReaction(int $reactionId): bool
    {
        [$owner, $repo] = explode('/', $this->fullName);

        $response = $this->connector->send(
            new DeleteCommentReactionRequest($owner, $repo, $this->commentId, $reactionId)
        );

        return $response->status() === 204;
    }

    /**
     * Fetch comment from API.
     */
    protected function fetch(): Comment
    {
        [$owner, $repo] = explode('/', $this->fullName);

        $response = $this->connector->send(
            new GetCommentRequest($owner, $repo, $this->commentId)
        );

        return Comment::fromArray($response->json());
    }
}
