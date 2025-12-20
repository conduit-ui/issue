<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Contracts\IssueQueryInterface;
use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Requests\Issues\ListIssuesRequest;
use DateTime;
use DateTimeInterface;
use Illuminate\Support\Collection;

class IssueQuery implements IssueQueryInterface
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
     * Filter issues by state (interface method).
     */
    public function state(string $state): self
    {
        $this->filters['state'] = $state;

        return $this;
    }

    /**
     * Filter issues by state (alias for backward compatibility).
     */
    public function whereState(string $state): self
    {
        return $this->state($state);
    }

    /**
     * Filter for open issues.
     */
    public function whereOpen(): self
    {
        return $this->state('open');
    }

    /**
     * Filter for closed issues.
     */
    public function whereClosed(): self
    {
        return $this->state('closed');
    }

    /**
     * Filter issues by labels (interface method).
     *
     * @param  array<string>|string  $labels
     */
    public function labels(array|string $labels): self
    {
        if (is_array($labels)) {
            $this->filters['labels'] = implode(',', $labels);
        } else {
            $this->filters['labels'] = $labels;
        }

        return $this;
    }

    /**
     * Filter by a single label (alias for backward compatibility).
     */
    public function whereLabel(string $label): self
    {
        return $this->labels($label);
    }

    /**
     * Filter by multiple labels (alias for backward compatibility).
     *
     * @param  array<string>  $labels
     */
    public function whereLabels(array $labels): self
    {
        return $this->labels($labels);
    }

    /**
     * Filter issues by assignee username (interface method).
     */
    public function assignee(string $username): self
    {
        $this->filters['assignee'] = $username;

        return $this;
    }

    /**
     * Filter by assignee (alias for backward compatibility).
     */
    public function assignedTo(string $username): self
    {
        return $this->assignee($username);
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
     * Filter issues by creator username (interface method).
     */
    public function creator(string $username): self
    {
        $this->filters['creator'] = $username;

        return $this;
    }

    /**
     * Filter by creator (alias for backward compatibility).
     */
    public function createdBy(string $username): self
    {
        return $this->creator($username);
    }

    /**
     * Filter issues mentioning a specific user (interface method).
     */
    public function mentioned(string $username): self
    {
        $this->filters['mentioned'] = $username;

        return $this;
    }

    /**
     * Filter by mentioned user (alias for backward compatibility).
     */
    public function mentioning(string $username): self
    {
        return $this->mentioned($username);
    }

    /**
     * Filter issues updated since a given date (interface method).
     */
    public function since(string|DateTimeInterface $date): self
    {
        $this->filters['since'] = $this->formatDate($date);

        return $this;
    }

    /**
     * Filter by created after date (alias for backward compatibility).
     */
    public function createdAfter(string|DateTime $date): self
    {
        return $this->since($date);
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
     * Sort issues by created, updated, or comments (interface method).
     */
    public function sort(string $field): self
    {
        $this->filters['sort'] = $field;

        return $this;
    }

    /**
     * Set sort direction (asc or desc) (interface method).
     */
    public function direction(string $direction): self
    {
        $this->filters['direction'] = $direction;

        return $this;
    }

    /**
     * Sort by field and direction (convenience method).
     */
    public function orderBy(string $field, string $direction = 'asc'): self
    {
        return $this->sort($field)->direction($direction);
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
    protected function formatDate(string|DateTimeInterface $date): string
    {
        if ($date instanceof DateTimeInterface) {
            return $date->format('c');
        }

        // Assume string is already in correct format or parse it
        $dateTime = new DateTime($date);

        return $dateTime->format('c');
    }
}
