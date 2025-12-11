<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Issue;
use Illuminate\Support\Collection;

interface ManagesIssuesInterface
{
    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\GithubIssues\Data\Issue>
     */
    public function listIssues(string $owner, string $repo, array $filters = []): Collection;

    public function getIssue(string $owner, string $repo, int $issueNumber): Issue;

    public function createIssue(string $owner, string $repo, array $data): Issue;

    public function updateIssue(string $owner, string $repo, int $issueNumber, array $data): Issue;

    public function closeIssue(string $owner, string $repo, int $issueNumber): Issue;

    public function reopenIssue(string $owner, string $repo, int $issueNumber): Issue;
}
