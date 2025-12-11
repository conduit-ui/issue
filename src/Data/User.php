<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Data;

readonly class User
{
    public function __construct(
        public int $id,
        public string $login,
        public string $avatarUrl,
        public string $htmlUrl,
        public string $type,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            login: $data['login'],
            avatarUrl: $data['avatar_url'],
            htmlUrl: $data['html_url'],
            type: $data['type'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'avatar_url' => $this->avatarUrl,
            'html_url' => $this->htmlUrl,
            'type' => $this->type,
        ];
    }
}
