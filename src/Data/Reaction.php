<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Data;

use DateTime;

readonly class Reaction
{
    private const VALID_TYPES = ['+1', '-1', 'laugh', 'confused', 'heart', 'hooray', 'rocket', 'eyes'];

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

    /**
     * @return array<int, string>
     */
    public static function validTypes(): array
    {
        return self::VALID_TYPES;
    }

    public static function isValidType(string $type): bool
    {
        return in_array($type, self::VALID_TYPES, true);
    }

    public function isThumbsUp(): bool
    {
        return $this->content === '+1';
    }

    public function isThumbsDown(): bool
    {
        return $this->content === '-1';
    }

    public function isLaugh(): bool
    {
        return $this->content === 'laugh';
    }

    public function isConfused(): bool
    {
        return $this->content === 'confused';
    }

    public function isHeart(): bool
    {
        return $this->content === 'heart';
    }

    public function isHooray(): bool
    {
        return $this->content === 'hooray';
    }

    public function isRocket(): bool
    {
        return $this->content === 'rocket';
    }

    public function isEyes(): bool
    {
        return $this->content === 'eyes';
    }
}
