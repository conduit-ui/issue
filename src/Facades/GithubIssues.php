<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Facades;

use ConduitUI\Issue\Services\IssuesService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \ConduitUI\Issue\Services\IssueInstance find(string $fullName, int $number)
 * @method static \Illuminate\Support\Collection listIssues(string $owner, string $repo, array $filters = [])
 * @method static \ConduitUI\GithubIssues\Data\Issue getIssue(string $owner, string $repo, int $issueNumber)
 * @method static \ConduitUI\GithubIssues\Data\Issue createIssue(string $owner, string $repo, array $data)
 * @method static \ConduitUI\GithubIssues\Data\Issue updateIssue(string $owner, string $repo, int $issueNumber, array $data)
 * @method static \ConduitUI\GithubIssues\Data\Issue closeIssue(string $owner, string $repo, int $issueNumber)
 * @method static \ConduitUI\GithubIssues\Data\Issue reopenIssue(string $owner, string $repo, int $issueNumber)
 * @method static \ConduitUI\GithubIssues\Data\Issue addAssignees(string $owner, string $repo, int $issueNumber, array $assignees)
 * @method static \ConduitUI\GithubIssues\Data\Issue removeAssignees(string $owner, string $repo, int $issueNumber, array $assignees)
 * @method static \ConduitUI\GithubIssues\Data\Issue assignIssue(string $owner, string $repo, int $issueNumber, string $assignee)
 * @method static \ConduitUI\GithubIssues\Data\Issue unassignIssue(string $owner, string $repo, int $issueNumber, string $assignee)
 * @method static \ConduitUI\GithubIssues\Data\Issue addLabels(string $owner, string $repo, int $issueNumber, array $labels)
 * @method static \ConduitUI\GithubIssues\Data\Issue removeLabels(string $owner, string $repo, int $issueNumber, array $labels)
 * @method static \ConduitUI\GithubIssues\Data\Issue addLabel(string $owner, string $repo, int $issueNumber, string $label)
 * @method static \ConduitUI\GithubIssues\Data\Issue removeLabel(string $owner, string $repo, int $issueNumber, string $label)
 * @method static \ConduitUI\GithubIssues\Data\Issue replaceAllLabels(string $owner, string $repo, int $issueNumber, array $labels)
 * @method static \ConduitUI\GithubIssues\Data\Issue removeAllLabels(string $owner, string $repo, int $issueNumber)
 */
class GithubIssues extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return IssuesService::class;
    }
}
