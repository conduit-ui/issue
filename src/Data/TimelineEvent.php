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
        public ?string $body = null,
        public ?string $commitId = null,
        public ?string $commitUrl = null,
        public ?Label $label = null,
        public ?User $assignee = null,
        public ?string $milestone = null,
        public ?string $rename = null,
        public ?array $source = null,
        public ?string $state = null,
        public ?string $stateReason = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            event: $data['event'],
            actor: isset($data['actor']) && $data['actor'] !== null ? User::fromArray($data['actor']) : null,
            createdAt: new DateTime($data['created_at']),
            body: $data['body'] ?? null,
            commitId: $data['commit_id'] ?? null,
            commitUrl: $data['commit_url'] ?? null,
            label: isset($data['label']) && $data['label'] !== null ? Label::fromArray($data['label']) : null,
            assignee: isset($data['assignee']) && $data['assignee'] !== null ? User::fromArray($data['assignee']) : null,
            milestone: $data['milestone']['title'] ?? null,
            rename: $data['rename']['from'] ?? $data['rename']['to'] ?? null,
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
            'body' => $this->body,
            'commit_id' => $this->commitId,
            'commit_url' => $this->commitUrl,
            'label' => $this->label?->toArray(),
            'assignee' => $this->assignee?->toArray(),
            'milestone' => $this->milestone,
            'rename' => $this->rename,
            'source' => $this->source,
            'state' => $this->state,
            'state_reason' => $this->stateReason,
        ], fn ($value) => $value !== null);
    }

    public function isComment(): bool
    {
        return $this->event === 'commented';
    }

    public function isCrossReference(): bool
    {
        return $this->event === 'cross-referenced';
    }

    public function isCommit(): bool
    {
        return $this->event === 'committed';
    }

    public function isReview(): bool
    {
        return $this->event === 'reviewed';
    }
}
