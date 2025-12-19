<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Requests\Issues\ListIssuesRequest;
use DateTime;
use Illuminate\Support\Collection;

class IssueQuery
{
    /**
     * @var array<string, mixed>
     */
    protected array $filters = [];

    public function __construct(
        private readonly Connector $connector,
        private readonly string $owner,
        private readonly string $repo
    ) {}

    /**
     * Filter issues by state.
     */
    public function whereState(string $state): self
    {
        $this->filters['state'] = $state;

        return $this;
    }

    /**
     * Filter for open issues.
     */
    public function whereOpen(): self
    {
        return $this->whereState('open');
    }

    /**
     * Filter for closed issues.
     */
    public function whereClosed(): self
    {
        return $this->whereState('closed');
    }

    /**
     * Filter by a single label.
     */
    public function whereLabel(string $label): self
    {
        $this->filters['labels'] = $label;

        return $this;
    }

    /**
     * Filter by multiple labels (comma-separated).
     */
    public function whereLabels(array $labels): self
    {
        $this->filters['labels'] = implode(',', $labels);

        return $this;
    }

    /**
     * Filter by assignee.
     */
    public function assignedTo(string $username): self
    {
        $this->filters['assignee'] = $username;

        return $this;
    }

    /**
     * Filter for unassigned issues.
     */
    public function whereUnassigned(): self
    {
        $this->filters['assignee'] = 'none';

        return $this;
    }

    /**
     * Filter by creator.
     */
    public function createdBy(string $username): self
    {
        $this->filters['creator'] = $username;

        return $this;
    }

    /**
     * Filter by mentioned user.
     */
    public function mentioning(string $username): self
    {
        $this->filters['mentioned'] = $username;

        return $this;
    }

    /**
     * Filter by created after date.
     */
    public function createdAfter(string|DateTime $date): self
    {
        $this->filters['since'] = $this->formatDate($date);

        return $this;
    }

    /**
     * Filter by updated before date (using updated_at for staleness check).
     */
    public function updatedBefore(string|DateTime $date): self
    {
        // Store this filter for client-side filtering since GitHub API doesn't support it directly
        $this->filters['updated_before'] = $this->formatDate($date);

        return $this;
    }

    /**
     * Filter issues older than N days (updated_at).
     */
    public function older(int $days): self
    {
        $date = new DateTime;
        $date->modify("-{$days} days");

        return $this->updatedBefore($date);
    }

    /**
     * Sort by field and direction.
     */
    public function orderBy(string $field, string $direction = 'asc'): self
    {
        $this->filters['sort'] = $field;
        $this->filters['direction'] = $direction;

        return $this;
    }

    /**
     * Sort by created date.
     */
    public function orderByCreated(string $direction = 'desc'): self
    {
        return $this->orderBy('created', $direction);
    }

    /**
     * Sort by updated date.
     */
    public function orderByUpdated(string $direction = 'desc'): self
    {
        return $this->orderBy('updated', $direction);
    }

    /**
     * Set per page limit.
     */
    public function perPage(int $perPage): self
    {
        $this->filters['per_page'] = $perPage;

        return $this;
    }

    /**
     * Set page number.
     */
    public function page(int $page): self
    {
        $this->filters['page'] = $page;

        return $this;
    }

    /**
     * Execute the query and get all issues.
     *
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Issue>
     */
    public function get(): Collection
    {
        // Remove client-side filters before sending request
        $apiFilters = $this->filters;
        $clientFilters = [];

        if (isset($apiFilters['updated_before'])) {
            $clientFilters['updated_before'] = $apiFilters['updated_before'];
            unset($apiFilters['updated_before']);
        }

        $response = $this->connector->send(
            new ListIssuesRequest($this->owner, $this->repo, $apiFilters)
        );

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        $collection = collect($items)
            ->map(fn (array $data): Issue => Issue::fromArray($data));

        // Apply client-side filters
        if (isset($clientFilters['updated_before'])) {
            /** @var string $updatedBeforeString */
            $updatedBeforeString = $clientFilters['updated_before'];
            $updatedBefore = new DateTime($updatedBeforeString);
            $collection = $collection->filter(fn (Issue $issue): bool => $issue->updatedAt <= $updatedBefore);
        }

        return $collection;
    }

    /**
     * Get the first issue.
     */
    public function first(): ?Issue
    {
        return $this->get()->first();
    }

    /**
     * Count the number of issues.
     */
    public function count(): int
    {
        return $this->get()->count();
    }

    /**
     * Check if any issues exist.
     */
    public function exists(): bool
    {
        return $this->get()->isNotEmpty();
    }

    /**
     * Format date to ISO 8601 format.
     */
    protected function formatDate(string|DateTime $date): string
    {
        if ($date instanceof DateTime) {
            return $date->format('c');
        }

        // Assume string is already in correct format or parse it
        $dateTime = new DateTime($date);

        return $dateTime->format('c');
    }
}
