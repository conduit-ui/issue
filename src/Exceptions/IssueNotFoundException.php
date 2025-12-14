<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Exceptions;

class IssueNotFoundException extends GithubApiException
{
    public static function forIssue(string $owner, string $repo, int $issueNumber): self
    {
        return new self(
            message: "Issue not found: {$owner}/{$repo}#{$issueNumber}",
            code: 404,
            context: ['owner' => $owner, 'repo' => $repo, 'issue_number' => $issueNumber]
        );
    }
}
