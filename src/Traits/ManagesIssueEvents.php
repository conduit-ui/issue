<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Event;
use ConduitUI\Issue\Data\TimelineEvent;
use Illuminate\Support\Collection;

trait ManagesIssueEvents
{
    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Event>
     */
    public function listIssueEvents(string $owner, string $repo, int $issueNumber, array $filters = []): Collection
    {
        $response = $this->connector->send(
            $this->connector->get("/repos/{$owner}/{$repo}/issues/{$issueNumber}/events", $filters)
        );

        return collect($response->json())
            ->map(fn (array $data) => Event::fromArray($data));
    }

    public function getIssueEvent(string $owner, string $repo, int $eventId): Event
    {
        $response = $this->connector->send(
            $this->connector->get("/repos/{$owner}/{$repo}/issues/events/{$eventId}")
        );

        return Event::fromArray($response->json());
    }

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\TimelineEvent>
     */
    public function listIssueTimeline(string $owner, string $repo, int $issueNumber, array $filters = []): Collection
    {
        $response = $this->connector->send(
            $this->connector->get("/repos/{$owner}/{$repo}/issues/{$issueNumber}/timeline", $filters)
        );

        return collect($response->json())
            ->map(fn (array $data) => TimelineEvent::fromArray($data));
    }
}
