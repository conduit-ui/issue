<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Event;
use Illuminate\Support\Collection;

interface ManagesIssueEventsInterface
{
    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Event>
     */
    public function listIssueEvents(string $owner, string $repo, int $issueNumber, array $filters = []): Collection;

    public function getIssueEvent(string $owner, string $repo, int $eventId): Event;

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\TimelineEvent>
     */
    public function listIssueTimeline(string $owner, string $repo, int $issueNumber, array $filters = []): Collection;
}
