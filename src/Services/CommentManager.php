<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Requests\Comments\CreateCommentRequest;
use ConduitUI\Issue\Requests\Comments\DeleteCommentRequest;
use ConduitUI\Issue\Requests\Comments\GetCommentRequest;
use ConduitUI\Issue\Requests\Comments\ListCommentsRequest;
use ConduitUI\Issue\Requests\Comments\UpdateCommentRequest;
use Illuminate\Support\Collection;

final class CommentManager
{
    public function __construct(
        protected readonly Connector $connector,
        protected readonly string $fullName,
        protected readonly int $issueNumber,
    ) {}

    /**
     * Get all comments for the issue.
     *
     * @return \Illuminate\Support\Collection<int, Comment>
     */
    public function all(): Collection
    {
        [$owner, $repo] = explode('/', $this->fullName);

        $response = $this->connector->send(
            new ListCommentsRequest($owner, $repo, $this->issueNumber)
        );

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return collect($items)
            ->map(fn (array $comment): Comment => Comment::fromArray($comment));
    }

    /**
     * Get a specific comment by ID.
     */
    public function find(int $commentId): Comment
    {
        [$owner, $repo] = explode('/', $this->fullName);

        $response = $this->connector->send(
            new GetCommentRequest($owner, $repo, $commentId)
        );

        return Comment::fromArray($response->json());
    }

    /**
     * Create a new comment.
     */
    public function create(string $body): CommentInstance
    {
        [$owner, $repo] = explode('/', $this->fullName);

        $response = $this->connector->send(
            new CreateCommentRequest($owner, $repo, $this->issueNumber, $body)
        );

        $comment = Comment::fromArray($response->json());

        return new CommentInstance(
            connector: $this->connector,
            fullName: $this->fullName,
            commentId: $comment->id,
        );
    }

    /**
     * Update a comment.
     */
    public function update(int $commentId, string $body): Comment
    {
        [$owner, $repo] = explode('/', $this->fullName);

        $response = $this->connector->send(
            new UpdateCommentRequest($owner, $repo, $commentId, $body)
        );

        return Comment::fromArray($response->json());
    }

    /**
     * Delete a comment.
     */
    public function delete(int $commentId): bool
    {
        [$owner, $repo] = explode('/', $this->fullName);

        $response = $this->connector->send(
            new DeleteCommentRequest($owner, $repo, $commentId)
        );

        return $response->status() === 204;
    }

    /**
     * Get a comment instance for chaining.
     */
    public function comment(int $commentId): CommentInstance
    {
        return new CommentInstance(
            connector: $this->connector,
            fullName: $this->fullName,
            commentId: $commentId,
        );
    }
}
