<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

/**
 * Interface for the unified Issues facade.
 *
 * Provides high-level access to all manager interfaces through
 * a single entry point, enabling clean and consistent API usage.
 */
interface IssuesFacadeInterface
{
    /**
     * Get the issue manager for a repository.
     */
    public function issues(string $owner, string $repo): IssueManagerInterface;

    /**
     * Get the comment manager for a repository.
     */
    public function comments(string $owner, string $repo): CommentManagerInterface;

    /**
     * Get the label manager for a repository.
     */
    public function labels(string $owner, string $repo): LabelManagerInterface;

    /**
     * Get the reaction manager for a repository.
     */
    public function reactions(string $owner, string $repo): ReactionManagerInterface;

    /**
     * Get the milestone manager for a repository.
     */
    public function milestones(string $owner, string $repo): MilestoneManagerInterface;

    /**
     * Get the repository label manager for a repository.
     */
    public function repositoryLabels(string $owner, string $repo): RepositoryLabelManagerInterface;
}
