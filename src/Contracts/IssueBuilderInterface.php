<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Issue;

/**
 * Interface for building GitHub issues.
 *
 * Provides a fluent interface for constructing issue data
 * before creation, ensuring all required fields are set.
 */
interface IssueBuilderInterface
{
    /**
     * Set the issue title.
     */
    public function title(string $title): self;

    /**
     * Set the issue body.
     */
    public function body(string $body): self;

    /**
     * Set the issue assignees.
     *
     * @param  array<string>  $assignees
     */
    public function assignees(array $assignees): self;

    /**
     * Add a single assignee.
     */
    public function assignee(string $assignee): self;

    /**
     * Set the issue labels.
     *
     * @param  array<string>  $labels
     */
    public function labels(array $labels): self;

    /**
     * Add a single label.
     */
    public function label(string $label): self;

    /**
     * Set the milestone number.
     */
    public function milestone(int $milestoneNumber): self;

    /**
     * Create the issue and return the Issue data object.
     */
    public function create(): Issue;

    /**
     * Get the raw data array without creating the issue.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
