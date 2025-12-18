<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Requests\Comments\CreateCommentRequest;
use ConduitUI\Issue\Requests\Comments\DeleteCommentRequest;
use ConduitUI\Issue\Requests\Comments\GetCommentRequest;
use ConduitUI\Issue\Requests\Comments\ListCommentsRequest;
use ConduitUI\Issue\Requests\Comments\UpdateCommentRequest;
use Illuminate\Support\Collection;

trait ManagesIssueComments
{
    use HandlesApiErrors;
    use ValidatesInput;

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Comment>
     */
    public function listComments(string $owner, string $repo, int $issueNumber, array $filters = []): Collection
    {
        $this->validateRepository($owner, $repo);
        $this->validateIssueNumber($issueNumber);

        $response = $this->connector->send(
            new ListCommentsRequest($owner, $repo, $issueNumber, $filters)
        );

        $this->handleApiResponse($response, $owner, $repo, $issueNumber);

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return collect($items)
            ->map(fn (array $data): Comment => Comment::fromArray($data)->withContext($owner, $repo, $this));
    }

    public function getComment(string $owner, string $repo, int $commentId): Comment
    {
        $this->validateRepository($owner, $repo);
        $this->validateCommentId($commentId);

        $response = $this->connector->send(
            new GetCommentRequest($owner, $repo, $commentId)
        );

        $this->handleApiResponse($response, $owner, $repo);

        return Comment::fromArray($response->json())->withContext($owner, $repo, $this);
    }

    public function createComment(string $owner, string $repo, int $issueNumber, string $body): Comment
    {
        $this->validateRepository($owner, $repo);
        $this->validateIssueNumber($issueNumber);
        $this->validateNotEmpty($body, 'body');

        $response = $this->connector->send(
            new CreateCommentRequest($owner, $repo, $issueNumber, $body)
        );

        $this->handleApiResponse($response, $owner, $repo, $issueNumber);

        return Comment::fromArray($response->json())->withContext($owner, $repo, $this);
    }

    public function updateComment(string $owner, string $repo, int $commentId, string $body): Comment
    {
        $this->validateRepository($owner, $repo);
        $this->validateCommentId($commentId);
        $this->validateNotEmpty($body, 'body');

        $response = $this->connector->send(
            new UpdateCommentRequest($owner, $repo, $commentId, $body)
        );

        $this->handleApiResponse($response, $owner, $repo);

        return Comment::fromArray($response->json())->withContext($owner, $repo, $this);
    }

    public function deleteComment(string $owner, string $repo, int $commentId): bool
    {
        $this->validateRepository($owner, $repo);
        $this->validateCommentId($commentId);

        $response = $this->connector->send(
            new DeleteCommentRequest($owner, $repo, $commentId)
        );

        $this->handleApiResponse($response, $owner, $repo);

        return $response->status() === 204;
    }
}
