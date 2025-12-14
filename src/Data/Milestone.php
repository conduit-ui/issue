<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Data;

use DateTime;

readonly class Milestone
{
    public function __construct(
        public int $id,
        public int $number,
        public string $title,
        public ?string $description,
        public string $state,
        public User $creator,
        public int $openIssues,
        public int $closedIssues,
        public ?DateTime $dueOn,
        public DateTime $createdAt,
        public DateTime $updatedAt,
        public ?DateTime $closedAt,
        public string $htmlUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            number: $data['number'],
            title: $data['title'],
            description: $data['description'] ?? null,
            state: $data['state'],
            creator: User::fromArray($data['creator']),
            openIssues: $data['open_issues'],
            closedIssues: $data['closed_issues'],
            dueOn: $data['due_on'] ? new DateTime($data['due_on']) : null,
            createdAt: new DateTime($data['created_at']),
            updatedAt: new DateTime($data['updated_at']),
            closedAt: $data['closed_at'] ? new DateTime($data['closed_at']) : null,
            htmlUrl: $data['html_url'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'title' => $this->title,
            'description' => $this->description,
            'state' => $this->state,
            'creator' => $this->creator->toArray(),
            'open_issues' => $this->openIssues,
            'closed_issues' => $this->closedIssues,
            'due_on' => $this->dueOn?->format('c'),
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt->format('c'),
            'closed_at' => $this->closedAt?->format('c'),
            'html_url' => $this->htmlUrl,
        ];
    }

    public function isOpen(): bool
    {
        return $this->state === 'open';
    }

    public function isClosed(): bool
    {
        return $this->state === 'closed';
    }

    public function isOverdue(): bool
    {
        if ($this->dueOn === null || $this->isClosed()) {
            return false;
        }

        return $this->dueOn < new DateTime;
    }

    public function completionPercentage(): float
    {
        $total = $this->openIssues + $this->closedIssues;

        if ($total === 0) {
            return 0.0;
        }

        return round(($this->closedIssues / $total) * 100, 2);
    }
}
