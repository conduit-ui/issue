<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Data;

use DateTime;

readonly class Reaction
{
    public function __construct(
        public int $id,
        public string $content,
        public User $user,
        public DateTime $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            content: $data['content'],
            user: User::fromArray($data['user']),
            createdAt: new DateTime($data['created_at']),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'user' => $this->user->toArray(),
            'created_at' => $this->createdAt->format('c'),
        ];
    }
}
