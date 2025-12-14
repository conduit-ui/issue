<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Data;

use DateTime;

readonly class IssueEvent
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
        public ?string $milestone = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            event: $data['event'],
            actor: isset($data['actor']) && $data['actor'] !== null ? User::fromArray($data['actor']) : null,
            createdAt: new DateTime($data['created_at']),
            commitId: $data['commit_id'] ?? null,
            commitUrl: $data['commit_url'] ?? null,
            label: isset($data['label']) && $data['label'] !== null ? Label::fromArray($data['label']) : null,
            assignee: isset($data['assignee']) && $data['assignee'] !== null ? User::fromArray($data['assignee']) : null,
            milestone: $data['milestone']['title'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'event' => $this->event,
            'actor' => $this->actor?->toArray(),
            'created_at' => $this->createdAt->format('c'),
            'commit_id' => $this->commitId,
            'commit_url' => $this->commitUrl,
            'label' => $this->label?->toArray(),
            'assignee' => $this->assignee?->toArray(),
            'milestone' => $this->milestone,
        ];
    }

    public function isLabelEvent(): bool
    {
        return in_array($this->event, ['labeled', 'unlabeled'], true);
    }

    public function isAssigneeEvent(): bool
    {
        return in_array($this->event, ['assigned', 'unassigned'], true);
    }

    public function isStateEvent(): bool
    {
        return in_array($this->event, ['closed', 'reopened'], true);
    }

    public function isMilestoneEvent(): bool
    {
        return in_array($this->event, ['milestoned', 'demilestoned'], true);
    }
}
