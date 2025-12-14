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
        public int $openIssues,
        public int $closedIssues,
        public DateTime $createdAt,
        public DateTime $updatedAt,
        public ?DateTime $closedAt,
        public ?DateTime $dueOn,
        public string $htmlUrl,
        public User $creator,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            number: $data['number'],
            title: $data['title'],
            description: $data['description'] ?? null,
            state: $data['state'],
            openIssues: $data['open_issues'],
            closedIssues: $data['closed_issues'],
            createdAt: new DateTime($data['created_at']),
            updatedAt: new DateTime($data['updated_at']),
            closedAt: $data['closed_at'] ? new DateTime($data['closed_at']) : null,
            dueOn: $data['due_on'] ? new DateTime($data['due_on']) : null,
            htmlUrl: $data['html_url'],
            creator: User::fromArray($data['creator']),
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
            'open_issues' => $this->openIssues,
            'closed_issues' => $this->closedIssues,
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt->format('c'),
            'closed_at' => $this->closedAt?->format('c'),
            'due_on' => $this->dueOn?->format('c'),
            'html_url' => $this->htmlUrl,
            'creator' => $this->creator->toArray(),
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
}
