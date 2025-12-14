<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Data;

use DateTime;

readonly class Event
{
    public function __construct(
        public int $id,
        public string $event,
        public ?User $actor,
        public ?string $commitId,
        public ?string $commitUrl,
        public DateTime $createdAt,
        public ?Label $label = null,
        public ?User $assignee = null,
        public ?array $milestone = null,
        public ?array $rename = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            event: $data['event'],
            actor: isset($data['actor']) ? User::fromArray($data['actor']) : null,
            commitId: $data['commit_id'] ?? null,
            commitUrl: $data['commit_url'] ?? null,
            createdAt: new DateTime($data['created_at']),
            label: isset($data['label']) ? Label::fromArray($data['label']) : null,
            assignee: isset($data['assignee']) ? User::fromArray($data['assignee']) : null,
            milestone: $data['milestone'] ?? null,
            rename: $data['rename'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'event' => $this->event,
            'actor' => $this->actor?->toArray(),
            'commit_id' => $this->commitId,
            'commit_url' => $this->commitUrl,
            'created_at' => $this->createdAt->format('c'),
            'label' => $this->label?->toArray(),
            'assignee' => $this->assignee?->toArray(),
            'milestone' => $this->milestone,
            'rename' => $this->rename,
        ], fn ($value) => $value !== null);
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
}
