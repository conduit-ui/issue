<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Reaction;
use ConduitUI\Issue\Requests\Reactions\CreateCommentReactionRequest;
use ConduitUI\Issue\Requests\Reactions\DeleteCommentReactionRequest;
use ConduitUI\Issue\Requests\Reactions\ListCommentReactionsRequest;
use Illuminate\Support\Collection;
use InvalidArgumentException;

trait ManagesCommentReactions
{
    use HandlesApiErrors;
    use ValidatesInput;

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Reaction>
     */
    public function listCommentReactions(string $owner, string $repo, int $commentId, array $filters = []): Collection
    {
        $this->validateRepository($owner, $repo);
        $this->validateCommentId($commentId);

        $response = $this->connector->send(
            new ListCommentReactionsRequest($owner, $repo, $commentId, $filters)
        );

        $this->handleApiResponse($response, $owner, $repo);

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return collect($items)
            ->map(fn (array $data): Reaction => Reaction::fromArray($data));
    }

    public function createCommentReaction(string $owner, string $repo, int $commentId, string $content): Reaction
    {
        $this->validateRepository($owner, $repo);
        $this->validateCommentId($commentId);
        $this->validateReactionContent($content);

        $response = $this->connector->send(
            new CreateCommentReactionRequest($owner, $repo, $commentId, $content)
        );

        $this->handleApiResponse($response, $owner, $repo);

        return Reaction::fromArray($response->json());
    }

    public function deleteCommentReaction(string $owner, string $repo, int $commentId, int $reactionId): bool
    {
        $this->validateRepository($owner, $repo);
        $this->validateCommentId($commentId);
        $this->validateReactionId($reactionId);

        $response = $this->connector->send(
            new DeleteCommentReactionRequest($owner, $repo, $commentId, $reactionId)
        );

        $this->handleApiResponse($response, $owner, $repo);

        return $response->status() === 204;
    }

    protected function validateReactionContent(string $content): void
    {
        if (trim($content) === '') {
            throw new InvalidArgumentException('Reaction content cannot be empty');
        }

        if (! Reaction::isValidType($content)) {
            throw new InvalidArgumentException(
                'Reaction content must be one of: '.implode(', ', Reaction::validTypes())
            );
        }
    }

    protected function validateReactionId(int $reactionId): void
    {
        if ($reactionId < 1) {
            throw new InvalidArgumentException('Reaction ID must be positive');
        }
    }
}
