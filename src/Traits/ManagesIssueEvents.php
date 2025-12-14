<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\IssueEvent;
use ConduitUI\Issue\Data\TimelineEvent;
use ConduitUI\Issue\Requests\Events\ListIssueEventsRequest;
use ConduitUI\Issue\Requests\Events\ListIssueTimelineRequest;
use ConduitUI\Issue\Requests\Events\ListRepositoryIssueEventsRequest;
use Illuminate\Support\Collection;

trait ManagesIssueEvents
{
    use HandlesApiErrors;
    use ValidatesInput;

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\IssueEvent>
     */
    public function listIssueEvents(string $owner, string $repo, int $issueNumber, array $filters = []): Collection
    {
        $this->validateRepository($owner, $repo);
        $this->validateIssueNumber($issueNumber);

        $response = $this->connector->send(
            new ListIssueEventsRequest($owner, $repo, $issueNumber, $filters)
        );

        $this->handleApiResponse($response, $owner, $repo, $issueNumber);

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return collect($items)
            ->map(fn (array $data): IssueEvent => IssueEvent::fromArray($data));
    }

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\TimelineEvent>
     */
    public function listIssueTimeline(string $owner, string $repo, int $issueNumber, array $filters = []): Collection
    {
        $this->validateRepository($owner, $repo);
        $this->validateIssueNumber($issueNumber);

        $response = $this->connector->send(
            new ListIssueTimelineRequest($owner, $repo, $issueNumber, $filters)
        );

        $this->handleApiResponse($response, $owner, $repo, $issueNumber);

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return collect($items)
            ->map(fn (array $data): TimelineEvent => TimelineEvent::fromArray($data));
    }

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\IssueEvent>
     */
    public function listRepositoryEvents(string $owner, string $repo, array $filters = []): Collection
    {
        $this->validateRepository($owner, $repo);

        $response = $this->connector->send(
            new ListRepositoryIssueEventsRequest($owner, $repo, $filters)
        );

        $this->handleApiResponse($response, $owner, $repo);

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return collect($items)
            ->map(fn (array $data): IssueEvent => IssueEvent::fromArray($data));
    }
}
