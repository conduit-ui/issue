<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use Illuminate\Support\Collection;

interface ManagesIssueEventsInterface
{
    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\IssueEvent>
     */
    public function listIssueEvents(string $owner, string $repo, int $issueNumber, array $filters = []): Collection;

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\TimelineEvent>
     */
    public function listIssueTimeline(string $owner, string $repo, int $issueNumber, array $filters = []): Collection;

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\IssueEvent>
     */
    public function listRepositoryEvents(string $owner, string $repo, array $filters = []): Collection;
}
