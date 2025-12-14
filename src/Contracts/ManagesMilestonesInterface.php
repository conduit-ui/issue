<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Data\Milestone;
use Illuminate\Support\Collection;

interface ManagesMilestonesInterface
{
    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Milestone>
     */
    public function listMilestones(string $owner, string $repo, array $filters = []): Collection;

    public function getMilestone(string $owner, string $repo, int $milestoneNumber): Milestone;

    public function createMilestone(string $owner, string $repo, array $data): Milestone;

    public function updateMilestone(string $owner, string $repo, int $milestoneNumber, array $data): Milestone;

    public function deleteMilestone(string $owner, string $repo, int $milestoneNumber): void;

    public function assignIssueToMilestone(string $owner, string $repo, int $issueNumber, int $milestoneNumber): Issue;

    public function removeIssueFromMilestone(string $owner, string $repo, int $issueNumber): Issue;

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Issue>
     */
    public function listMilestoneIssues(string $owner, string $repo, int $milestoneNumber, array $filters = []): Collection;
}
