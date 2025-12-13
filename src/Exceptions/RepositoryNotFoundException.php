<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Exceptions;

class RepositoryNotFoundException extends GithubApiException
{
    public static function forRepo(string $owner, string $repo): self
    {
        return new self(
            message: "Repository not found: {$owner}/{$repo}",
            code: 404,
            context: ['owner' => $owner, 'repo' => $repo]
        );
    }
}
