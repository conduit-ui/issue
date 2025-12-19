<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Requests\Events\ListIssueTimelineRequest;
use Illuminate\Support\Collection;

final class TimelineQuery
{
    public function __construct(
        protected readonly Connector $connector,
        protected readonly string $fullName,
        protected readonly int $issueNumber,
    ) {}

    /**
     * Get all timeline events for an issue.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function get(): Collection
    {
        [$owner, $repo] = explode('/', $this->fullName);

        $response = $this->connector->send(
            new ListIssueTimelineRequest($owner, $repo, $this->issueNumber)
        );

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return collect($items);
    }

    /**
     * Get only specific event types.
     *
     * @param  string|array<int, string>  $types
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function ofType(string|array $types): Collection
    {
        $types = is_array($types) ? $types : [$types];

        $filtered = $this->get()
            ->filter(fn (array $event): bool => in_array($event['event'] ?? null, $types, true))
            ->values();

        /** @var \Illuminate\Support\Collection<int, array<string, mixed>> */
        return $filtered;
    }

    /**
     * Get only comment events.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function comments(): Collection
    {
        return $this->ofType('commented');
    }

    /**
     * Get only label events.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function labels(): Collection
    {
        return $this->ofType(['labeled', 'unlabeled']);
    }
}
