<?php

declare(strict_types=1);

namespace ConduitUI\Issue;

use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Data\Comment;
use Illuminate\Support\Collection;

/**
 * Agent-friendly static interface for GitHub Issues.
 *
 * Set your repo context once, then all operations are scoped automatically.
 * This is the interface designed for AI agents and automation.
 *
 * Usage:
 *   Issues::repo('owner/repo');     // Set context (cached)
 *   Issues::open()->get();          // Query open issues
 *   Issues::find(123);              // Get single issue
 *   Issues::close(123);             // Close issue
 *   Issues::update(123, [...]);     // Composite update
 */
class Issues
{
    private static ?IssueContext $context = null;
    private static ?string $cacheKey = 'github.issues.repo';

    /**
     * Set or get the current repository context.
     *
     * Once set, all subsequent calls are scoped to this repo.
     * The context is cached for the duration of the request/session.
     */
    public static function repo(?string $identifier = null): IssueContext
    {
        if ($identifier !== null) {
            static::$context = IssueContext::from($identifier);

            // Also cache it for persistence across requests if cache is available
            if (function_exists('cache')) {
                cache()->put(static::$cacheKey, $identifier);
            }
        }

        // Try to restore from cache if no context set
        if (static::$context === null && function_exists('cache')) {
            $cached = cache()->get(static::$cacheKey);
            if ($cached) {
                static::$context = IssueContext::from($cached);
            }
        }

        if (static::$context === null) {
            throw new \RuntimeException(
                'No repository context set. Call Issues::repo("owner/repo") first.'
            );
        }

        return static::$context;
    }

    /**
     * Clear the current repository context.
     */
    public static function forget(): void
    {
        static::$context = null;

        if (function_exists('cache')) {
            cache()->forget(static::$cacheKey);
        }
    }

    /**
     * Check if a repository context is set.
     */
    public static function hasContext(): bool
    {
        if (static::$context !== null) {
            return true;
        }

        if (function_exists('cache')) {
            return cache()->has(static::$cacheKey);
        }

        return false;
    }

    // =========================================================================
    // QUERY INTERFACE (delegated to Repository)
    // =========================================================================

    /**
     * Start a fluent query builder.
     */
    public static function query(): IssueQuery
    {
        return static::repo()->issues();
    }

    /**
     * Query open issues.
     */
    public static function open(): IssueQuery
    {
        return static::query()->open();
    }

    /**
     * Query closed issues.
     */
    public static function closed(): IssueQuery
    {
        return static::query()->closed();
    }

    /**
     * Query all issues regardless of state.
     */
    public static function all(): IssueQuery
    {
        return static::query()->all();
    }

    // =========================================================================
    // SINGLE ISSUE OPERATIONS
    // =========================================================================

    /**
     * Get a single issue by number.
     */
    public static function find(int $number): Issue
    {
        return static::repo()->find($number);
    }

    /**
     * Alias for find.
     */
    public static function get(int $number): Issue
    {
        return static::find($number);
    }

    /**
     * Create a new issue.
     */
    public static function create(string $title, ?string $body = null, array $labels = [], array $assignees = []): Issue
    {
        return static::repo()->create($title, $body, $labels, $assignees);
    }

    // =========================================================================
    // STATE OPERATIONS
    // =========================================================================

    /**
     * Close an issue.
     */
    public static function close(int $number): Issue
    {
        return static::repo()->close($number);
    }

    /**
     * Close multiple issues.
     */
    public static function closeMany(array $numbers): Collection
    {
        return static::repo()->closeMany($numbers);
    }

    /**
     * Reopen an issue.
     */
    public static function reopen(int $number): Issue
    {
        return static::repo()->reopen($number);
    }

    // =========================================================================
    // LABEL OPERATIONS
    // =========================================================================

    /**
     * Add labels to an issue.
     */
    public static function addLabels(int $number, array $labels): Issue
    {
        return static::repo()->addLabels($number, $labels);
    }

    /**
     * Remove labels from an issue.
     */
    public static function removeLabels(int $number, array $labels): Issue
    {
        return static::repo()->removeLabels($number, $labels);
    }

    /**
     * Set labels on an issue (replaces all).
     */
    public static function setLabels(int $number, array $labels): Issue
    {
        return static::repo()->setLabels($number, $labels);
    }

    // =========================================================================
    // ASSIGNEE OPERATIONS
    // =========================================================================

    /**
     * Assign users to an issue.
     */
    public static function assign(int $number, string|array $assignees): Issue
    {
        return static::repo()->assign($number, $assignees);
    }

    /**
     * Unassign users from an issue.
     */
    public static function unassign(int $number, string|array $assignees): Issue
    {
        return static::repo()->unassign($number, $assignees);
    }

    // =========================================================================
    // COMMENT OPERATIONS
    // =========================================================================

    /**
     * Get comments on an issue.
     *
     * @return Collection<int, Comment>
     */
    public static function comments(int $number): Collection
    {
        return static::repo()->comments($number);
    }

    /**
     * Add a comment to an issue.
     */
    public static function comment(int $number, string $body): Comment
    {
        return static::repo()->comment($number, $body);
    }

    // =========================================================================
    // COMPOSITE OPERATIONS (the agent-friendly stuff)
    // =========================================================================

    /**
     * Update an issue with multiple changes at once.
     *
     * This is what agents actually want - one call to do everything:
     *
     * Issues::update(123, [
     *     'title' => 'New title',
     *     'state' => 'closed',
     *     'add_labels' => ['bug', 'fixed'],
     *     'remove_labels' => ['needs-triage'],
     *     'add_assignees' => ['alice'],
     * ]);
     */
    public static function update(int $number, array $changes): Issue
    {
        return static::repo()->update($number, $changes);
    }

    /**
     * Triage an issue - common agent operation.
     *
     * Adds labels, assigns, and optionally comments in one call.
     */
    public static function triage(int $number, array $labels, string|array|null $assignees = null, ?string $comment = null): Issue
    {
        $changes = ['add_labels' => $labels];

        if ($assignees !== null) {
            $changes['add_assignees'] = is_array($assignees) ? $assignees : [$assignees];
        }

        $issue = static::update($number, $changes);

        if ($comment !== null) {
            static::comment($number, $comment);
        }

        return $issue;
    }

    /**
     * Resolve an issue - close with labels and comment.
     */
    public static function resolve(int $number, array $labels = ['resolved'], ?string $comment = null): Issue
    {
        if ($comment !== null) {
            static::comment($number, $comment);
        }

        return static::update($number, [
            'state' => 'closed',
            'add_labels' => $labels,
        ]);
    }
}
