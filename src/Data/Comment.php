<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Data;

use ConduitUI\Issue\Contracts\ManagesCommentReactionsInterface;
use DateTime;
use Illuminate\Support\Collection;

class Comment
{
    private ?string $owner = null;

    private ?string $repo = null;

    private ?ManagesCommentReactionsInterface $service = null;

    public function __construct(
        public readonly int $id,
        public readonly string $body,
        public readonly User $user,
        public readonly DateTime $createdAt,
        public readonly DateTime $updatedAt,
        public readonly string $htmlUrl,
        public readonly string $authorAssociation,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            body: $data['body'],
            user: User::fromArray($data['user']),
            createdAt: new DateTime($data['created_at']),
            updatedAt: new DateTime($data['updated_at']),
            htmlUrl: $data['html_url'],
            authorAssociation: $data['author_association'],
        );
    }

    public function withContext(string $owner, string $repo, ManagesCommentReactionsInterface $service): self
    {
        $this->owner = $owner;
        $this->repo = $repo;
        $this->service = $service;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'user' => $this->user->toArray(),
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt->format('c'),
            'html_url' => $this->htmlUrl,
            'author_association' => $this->authorAssociation,
        ];
    }

    /**
     * Add a reaction to this comment.
     */
    public function react(string $content): Reaction
    {
        [$service, $owner, $repo] = $this->getContext();

        return $service->createCommentReaction($owner, $repo, $this->id, $content);
    }

    /**
     * Get all reactions on this comment.
     *
     * @return Collection<int, Reaction>
     */
    public function reactions(array $filters = []): Collection
    {
        [$service, $owner, $repo] = $this->getContext();

        return $service->listCommentReactions($owner, $repo, $this->id, $filters);
    }

    /**
     * Remove a reaction from this comment.
     */
    public function unreact(int $reactionId): bool
    {
        [$service, $owner, $repo] = $this->getContext();

        return $service->deleteCommentReaction($owner, $repo, $this->id, $reactionId);
    }

    public function isOwner(): bool
    {
        return $this->authorAssociation === 'OWNER';
    }

    public function isMember(): bool
    {
        return $this->authorAssociation === 'MEMBER';
    }

    public function isContributor(): bool
    {
        return $this->authorAssociation === 'CONTRIBUTOR';
    }

    /**
     * @return array{ManagesCommentReactionsInterface, string, string}
     */
    private function getContext(): array
    {
        if ($this->service === null || $this->owner === null || $this->repo === null) {
            throw new \RuntimeException(
                'Comment context not set. Use withContext() or retrieve comments through the service.'
            );
        }

        return [$this->service, $this->owner, $this->repo];
    }
}
