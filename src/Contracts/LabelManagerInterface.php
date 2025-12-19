<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Issue;

/**
 * Interface for managing issue labels.
 *
 * Provides operations for adding, removing, and replacing
 * labels on GitHub issues.
 */
interface LabelManagerInterface
{
    /**
     * Add labels to an issue.
     *
     * @param  array<string>  $labels
     */
    public function add(int $issueNumber, array $labels): Issue;

    /**
     * Remove labels from an issue.
     *
     * @param  array<string>  $labels
     */
    public function remove(int $issueNumber, array $labels): Issue;

    /**
     * Replace all labels on an issue.
     *
     * @param  array<string>  $labels
     */
    public function replace(int $issueNumber, array $labels): Issue;

    /**
     * Remove all labels from an issue.
     */
    public function clear(int $issueNumber): Issue;
}
