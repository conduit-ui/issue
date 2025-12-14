<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Data;

use DateTime;

readonly class TimelineEvent
{
    public function __construct(
        public int $id,
        public string $event,
        public ?User $actor,
        public DateTime $createdAt,
        public ?string $commitId = null,
        public ?string $commitUrl = null,
        public ?Label $label = null,
        public ?User $assignee = null,
        public ?array $milestone = null,
        public ?array $rename = null,
        public ?string $body = null,
        public ?User $user = null,
        public ?DateTime $updatedAt = null,
        public ?string $authorAssociation = null,
        public ?array $source = null,
        public ?string $state = null,
        public ?string $stateReason = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            event: $data['event'],
            actor: isset($data['actor']) ? User::fromArray($data['actor']) : null,
            createdAt: new DateTime($data['created_at']),
            commitId: $data['commit_id'] ?? null,
            commitUrl: $data['commit_url'] ?? null,
            label: isset($data['label']) ? Label::fromArray($data['label']) : null,
            assignee: isset($data['assignee']) ? User::fromArray($data['assignee']) : null,
            milestone: $data['milestone'] ?? null,
            rename: $data['rename'] ?? null,
            body: $data['body'] ?? null,
            user: isset($data['user']) ? User::fromArray($data['user']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTime($data['updated_at']) : null,
            authorAssociation: $data['author_association'] ?? null,
            source: $data['source'] ?? null,
            state: $data['state'] ?? null,
            stateReason: $data['state_reason'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'event' => $this->event,
            'actor' => $this->actor?->toArray(),
            'created_at' => $this->createdAt->format('c'),
            'commit_id' => $this->commitId,
            'commit_url' => $this->commitUrl,
            'label' => $this->label?->toArray(),
            'assignee' => $this->assignee?->toArray(),
            'milestone' => $this->milestone,
            'rename' => $this->rename,
            'body' => $this->body,
            'user' => $this->user?->toArray(),
            'updated_at' => $this->updatedAt?->format('c'),
            'author_association' => $this->authorAssociation,
            'source' => $this->source,
            'state' => $this->state,
            'state_reason' => $this->stateReason,
        ], fn ($value) => $value !== null);
    }

    public function isComment(): bool
    {
        return $this->event === 'commented';
    }

    public function isLabelEvent(): bool
    {
        return in_array($this->event, ['labeled', 'unlabeled']);
    }

    public function isAssigneeEvent(): bool
    {
        return in_array($this->event, ['assigned', 'unassigned']);
    }

    public function isMilestoneEvent(): bool
    {
        return in_array($this->event, ['milestoned', 'demilestoned']);
    }

    public function isStateEvent(): bool
    {
        return in_array($this->event, ['closed', 'reopened']);
    }

    public function isCrossReferenced(): bool
    {
        return $this->event === 'cross-referenced';
    }
}
