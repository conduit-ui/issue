<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

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

    public function deleteMilestone(string $owner, string $repo, int $milestoneNumber): bool;

    public function closeMilestone(string $owner, string $repo, int $milestoneNumber): Milestone;

    public function reopenMilestone(string $owner, string $repo, int $milestoneNumber): Milestone;
}
