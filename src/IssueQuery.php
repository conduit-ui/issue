<?php

declare(strict_types=1);

namespace ConduitUI\Issue;

use ConduitUI\Issue\Data\Issue;
use Illuminate\Support\Collection;

/**
 * Fluent query builder for GitHub issues.
 *
 * Chainable methods that build up filter criteria before executing.
 * Feels like Eloquent, works with GitHub API.
 */
class IssueQuery
{
    private array $filters = [];

    public function __construct(
        private readonly IssueContext $context
    ) {}

    // =========================================================================
    // STATE FILTERS
    // =========================================================================

    /**
     * Filter by state.
     */
    public function state(string $state): self
    {
        $this->filters['state'] = $state;
        return $this;
    }

    /**
     * Only open issues.
     */
    public function open(): self
    {
        return $this->state('open');
    }

    /**
     * Only closed issues.
     */
    public function closed(): self
    {
        return $this->state('closed');
    }

    /**
     * All issues regardless of state.
     */
    public function all(): self
    {
        return $this->state('all');
    }

    // =========================================================================
    // LABEL FILTERS
    // =========================================================================

    /**
     * Filter by label(s).
     */
    public function labels(array $labels): self
    {
        $this->filters['labels'] = implode(',', $labels);
        return $this;
    }

    /**
     * Filter by a single label.
     */
    public function label(string $label): self
    {
        return $this->labels([$label]);
    }

    /**
     * Alias: whereLabel
     */
    public function whereLabel(string $label): self
    {
        return $this->label($label);
    }

    /**
     * Alias: whereLabels
     */
    public function whereLabels(array $labels): self
    {
        return $this->labels($labels);
    }

    // =========================================================================
    // ASSIGNEE FILTERS
    // =========================================================================

    /**
     * Filter by assignee.
     */
    public function assignee(string $username): self
    {
        $this->filters['assignee'] = $username;
        return $this;
    }

    /**
     * Filter for issues with no assignee.
     */
    public function unassigned(): self
    {
        return $this->assignee('none');
    }

    /**
     * Filter for issues assigned to anyone.
     */
    public function assigned(): self
    {
        return $this->assignee('*');
    }

    /**
     * Alias: whereAssignee
     */
    public function whereAssignee(string $username): self
    {
        return $this->assignee($username);
    }

    // =========================================================================
    // AUTHOR/CREATOR FILTERS
    // =========================================================================

    /**
     * Filter by creator.
     */
    public function creator(string $username): self
    {
        $this->filters['creator'] = $username;
        return $this;
    }

    /**
     * Alias: author
     */
    public function author(string $username): self
    {
        return $this->creator($username);
    }

    /**
     * Alias: whereAuthor
     */
    public function whereAuthor(string $username): self
    {
        return $this->creator($username);
    }

    // =========================================================================
    // MENTIONED FILTERS
    // =========================================================================

    /**
     * Filter by mentioned user.
     */
    public function mentioned(string $username): self
    {
        $this->filters['mentioned'] = $username;
        return $this;
    }

    // =========================================================================
    // DATE FILTERS
    // =========================================================================

    /**
     * Filter by issues updated since a date.
     */
    public function since(\DateTimeInterface|string $date): self
    {
        $this->filters['since'] = $date instanceof \DateTimeInterface
            ? $date->format('c')
            : $date;
        return $this;
    }

    /**
     * Issues updated in the last N days.
     */
    public function updatedInLast(int $days): self
    {
        return $this->since(now()->subDays($days));
    }

    /**
     * Alias for readable queries.
     */
    public function updatedAfter(\DateTimeInterface|string $date): self
    {
        return $this->since($date);
    }

    // =========================================================================
    // SORTING
    // =========================================================================

    /**
     * Sort by field.
     */
    public function sort(string $field, string $direction = 'desc'): self
    {
        $this->filters['sort'] = $field;
        $this->filters['direction'] = $direction;
        return $this;
    }

    /**
     * Sort by created date (newest first).
     */
    public function latest(): self
    {
        return $this->sort('created', 'desc');
    }

    /**
     * Sort by created date (oldest first).
     */
    public function oldest(): self
    {
        return $this->sort('created', 'asc');
    }

    /**
     * Sort by most recently updated.
     */
    public function recentlyUpdated(): self
    {
        return $this->sort('updated', 'desc');
    }

    /**
     * Sort by most comments.
     */
    public function mostComments(): self
    {
        return $this->sort('comments', 'desc');
    }

    // =========================================================================
    // PAGINATION
    // =========================================================================

    /**
     * Limit results per page.
     */
    public function perPage(int $count): self
    {
        $this->filters['per_page'] = min($count, 100); // GitHub max is 100
        return $this;
    }

    /**
     * Get specific page.
     */
    public function page(int $page): self
    {
        $this->filters['page'] = $page;
        return $this;
    }

    /**
     * Alias: take/limit
     */
    public function take(int $count): self
    {
        return $this->perPage($count);
    }

    public function limit(int $count): self
    {
        return $this->perPage($count);
    }

    // =========================================================================
    // MILESTONE FILTER
    // =========================================================================

    /**
     * Filter by milestone number.
     */
    public function milestone(int|string $milestone): self
    {
        $this->filters['milestone'] = $milestone;
        return $this;
    }

    /**
     * Issues with no milestone.
     */
    public function noMilestone(): self
    {
        return $this->milestone('none');
    }

    // =========================================================================
    // EXECUTION
    // =========================================================================

    /**
     * Execute the query and get results.
     */
    public function get(): Collection
    {
        return $this->context->service()->listIssues(
            $this->context->owner,
            $this->context->repo,
            $this->filters
        );
    }

    /**
     * Get the first result.
     */
    public function first(): ?Issue
    {
        return $this->take(1)->get()->first();
    }

    /**
     * Count matching issues (fetches first page only).
     */
    public function count(): int
    {
        return $this->get()->count();
    }

    /**
     * Check if any matching issues exist.
     */
    public function exists(): bool
    {
        return $this->first() !== null;
    }

    /**
     * Get just the issue numbers.
     */
    public function pluck(string $field = 'number'): Collection
    {
        return $this->get()->pluck($field);
    }

    /**
     * Get just issue numbers.
     */
    public function numbers(): Collection
    {
        return $this->pluck('number');
    }

    // =========================================================================
    // BULK OPERATIONS ON RESULTS
    // =========================================================================

    /**
     * Close all matching issues.
     */
    public function closeAll(): Collection
    {
        return $this->get()->map(
            fn (Issue $issue) => $this->context->close($issue->number)
        );
    }

    /**
     * Add labels to all matching issues.
     */
    public function addLabelsToAll(array $labels): Collection
    {
        return $this->get()->map(
            fn (Issue $issue) => $this->context->addLabels($issue->number, $labels)
        );
    }

    /**
     * Assign all matching issues to user(s).
     */
    public function assignAll(string|array $assignees): Collection
    {
        return $this->get()->map(
            fn (Issue $issue) => $this->context->assign($issue->number, $assignees)
        );
    }

    /**
     * Apply a callback to each matching issue.
     */
    public function each(callable $callback): Collection
    {
        return $this->get()->each($callback);
    }

    /**
     * Get the current filters (for debugging).
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
