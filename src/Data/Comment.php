<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Data;

use DateTime;

readonly class Comment
{
    public function __construct(
        public int $id,
        public string $body,
        public User $user,
        public DateTime $createdAt,
        public DateTime $updatedAt,
        public string $htmlUrl,
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
        );
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
        ];
    }
}
