<?php

declare(strict_types=1);

namespace ConduitUI\Issue;

use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Services\IssuesService;
use Illuminate\Support\Collection;

/**
 * Issue context - the tenant/scope for all issue operations.
 *
 * Once you have an IssueContext instance, all operations are scoped to it.
 * No more passing owner/repo to every method.
 *
 * Named IssueContext (not Repository) to avoid conflicts with conduit-ui/repo.
 */
class IssueContext
{
    private IssuesService $service;

    public function __construct(
        public readonly string $owner,
        public readonly string $repo,
        ?IssuesService $service = null
    ) {
        $this->service = $service ?? app(IssuesService::class);
    }

    /**
     * Create a repository context from "owner/repo" string.
     */
    public static function from(string $identifier): self
    {
        if (! str_contains($identifier, '/')) {
            throw new \InvalidArgumentException(
                "Repository identifier must be in 'owner/repo' format, got: {$identifier}"
            );
        }

        [$owner, $repo] = explode('/', $identifier, 2);

        return new self($owner, $repo);
    }

    /**
     * Get the full identifier string.
     */
    public function identifier(): string
    {
        return "{$this->owner}/{$this->repo}";
    }

    // =========================================================================
    // QUERY INTERFACE
    // =========================================================================

    /**
     * Start a fluent query for issues.
     */
    public function issues(): IssueQuery
    {
        return new IssueQuery($this);
    }

    // =========================================================================
    // SINGLE ISSUE OPERATIONS
    // =========================================================================

    /**
     * Get a single issue by number.
     */
    public function issue(int $number): Issue
    {
        return $this->service->getIssue($this->owner, $this->repo, $number);
    }

    /**
     * Alias for issue() - more Eloquent-like.
     */
    public function find(int $number): Issue
    {
        return $this->issue($number);
    }

    /**
     * Create a new issue.
     */
    public function create(string $title, ?string $body = null, array $labels = [], array $assignees = []): Issue
    {
        return $this->service->createIssue($this->owner, $this->repo, array_filter([
            'title' => $title,
            'body' => $body,
            'labels' => $labels ?: null,
            'assignees' => $assignees ?: null,
        ]));
    }

    // =========================================================================
    // ISSUE STATE OPERATIONS (scoped)
    // =========================================================================

    /**
     * Close an issue.
     */
    public function close(int $number): Issue
    {
        return $this->service->closeIssue($this->owner, $this->repo, $number);
    }

    /**
     * Close multiple issues at once.
     *
     * @param  array<int>  $numbers
     * @return Collection<int, Issue>
     */
    public function closeMany(array $numbers): Collection
    {
        return collect($numbers)->map(fn (int $n): Issue => $this->close($n));
    }

    /**
     * Reopen an issue.
     */
    public function reopen(int $number): Issue
    {
        return $this->service->reopenIssue($this->owner, $this->repo, $number);
    }

    // =========================================================================
    // LABEL OPERATIONS (scoped)
    // =========================================================================

    /**
     * Add labels to an issue.
     */
    public function addLabels(int $number, array $labels): Issue
    {
        return $this->service->addLabels($this->owner, $this->repo, $number, $labels);
    }

    /**
     * Remove labels from an issue.
     */
    public function removeLabels(int $number, array $labels): Issue
    {
        return $this->service->removeLabels($this->owner, $this->repo, $number, $labels);
    }

    /**
     * Replace all labels on an issue.
     */
    public function setLabels(int $number, array $labels): Issue
    {
        return $this->service->replaceAllLabels($this->owner, $this->repo, $number, $labels);
    }

    // =========================================================================
    // ASSIGNEE OPERATIONS (scoped)
    // =========================================================================

    /**
     * Assign users to an issue.
     */
    public function assign(int $number, string|array $assignees): Issue
    {
        $assignees = is_array($assignees) ? $assignees : [$assignees];

        return $this->service->addAssignees($this->owner, $this->repo, $number, $assignees);
    }

    /**
     * Unassign users from an issue.
     */
    public function unassign(int $number, string|array $assignees): Issue
    {
        $assignees = is_array($assignees) ? $assignees : [$assignees];

        return $this->service->removeAssignees($this->owner, $this->repo, $number, $assignees);
    }

    // =========================================================================
    // COMPOSITE OPERATIONS (the good stuff)
    // =========================================================================

    /**
     * Update an issue with multiple changes at once.
     *
     * This is what agents actually want - one call to do everything.
     */
    public function update(int $number, array $changes): Issue
    {
        $issue = null;

        // Handle state changes
        if (isset($changes['state'])) {
            $issue = $changes['state'] === 'closed'
                ? $this->close($number)
                : $this->reopen($number);
        }

        // Handle basic field updates
        $fieldUpdates = array_filter([
            'title' => $changes['title'] ?? null,
            'body' => $changes['body'] ?? null,
        ]);

        if (! empty($fieldUpdates)) {
            $issue = $this->service->updateIssue($this->owner, $this->repo, $number, $fieldUpdates);
        }

        // Handle label additions
        if (! empty($changes['add_labels'])) {
            $issue = $this->addLabels($number, $changes['add_labels']);
        }

        // Handle label removals
        if (! empty($changes['remove_labels'])) {
            $issue = $this->removeLabels($number, $changes['remove_labels']);
        }

        // Handle label replacement
        if (isset($changes['labels'])) {
            $issue = $this->setLabels($number, $changes['labels']);
        }

        // Handle assignee additions
        if (! empty($changes['add_assignees'])) {
            $issue = $this->assign($number, $changes['add_assignees']);
        }

        // Handle assignee removals
        if (! empty($changes['remove_assignees'])) {
            $issue = $this->unassign($number, $changes['remove_assignees']);
        }

        // If nothing was changed, just fetch the current state
        return $issue ?? $this->issue($number);
    }

    /**
     * Get the underlying service for advanced operations.
     */
    public function service(): IssuesService
    {
        return $this->service;
    }

    // =========================================================================
    // COMMENT OPERATIONS
    // =========================================================================

    /**
     * Get comments on an issue.
     *
     * @return Collection<int, Comment>
     */
    public function comments(int $number): Collection
    {
        $connector = $this->service->connector();
        $response = $connector->send(
            $connector->get("/repos/{$this->owner}/{$this->repo}/issues/{$number}/comments")
        );

        /** @var array<int, array<string, mixed>> $data */
        $data = $response->json();

        return collect($data)
            ->map(fn (array $item): Comment => Comment::fromArray($item));
    }

    /**
     * Add a comment to an issue.
     */
    public function comment(int $number, string $body): Comment
    {
        $connector = $this->service->connector();
        $response = $connector->send(
            $connector->post("/repos/{$this->owner}/{$this->repo}/issues/{$number}/comments", [
                'body' => $body,
            ])
        );

        /** @var array<string, mixed> $data */
        $data = $response->json();

        return Comment::fromArray($data);
    }
}
