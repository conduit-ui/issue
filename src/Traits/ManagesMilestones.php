<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Data\Milestone;
use Illuminate\Support\Collection;

trait ManagesMilestones
{
    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Milestone>
     */
    public function listMilestones(string $owner, string $repo, array $filters = []): Collection
    {
        $response = $this->connector->send(
            $this->connector->get("/repos/{$owner}/{$repo}/milestones", $filters)
        );

        return collect($response->json())
            ->map(fn (array $data) => Milestone::fromArray($data));
    }

    public function getMilestone(string $owner, string $repo, int $milestoneNumber): Milestone
    {
        $response = $this->connector->send(
            $this->connector->get("/repos/{$owner}/{$repo}/milestones/{$milestoneNumber}")
        );

        return Milestone::fromArray($response->json());
    }

    public function createMilestone(string $owner, string $repo, array $data): Milestone
    {
        $response = $this->connector->send(
            $this->connector->post("/repos/{$owner}/{$repo}/milestones", $data)
        );

        return Milestone::fromArray($response->json());
    }

    public function updateMilestone(string $owner, string $repo, int $milestoneNumber, array $data): Milestone
    {
        $response = $this->connector->send(
            $this->connector->patch("/repos/{$owner}/{$repo}/milestones/{$milestoneNumber}", $data)
        );

        return Milestone::fromArray($response->json());
    }

    public function deleteMilestone(string $owner, string $repo, int $milestoneNumber): void
    {
        $this->connector->send(
            $this->connector->delete("/repos/{$owner}/{$repo}/milestones/{$milestoneNumber}")
        );
    }

    public function assignIssueToMilestone(string $owner, string $repo, int $issueNumber, int $milestoneNumber): Issue
    {
        return $this->updateIssue($owner, $repo, $issueNumber, ['milestone' => $milestoneNumber]);
    }

    public function removeIssueFromMilestone(string $owner, string $repo, int $issueNumber): Issue
    {
        return $this->updateIssue($owner, $repo, $issueNumber, ['milestone' => null]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Issue>
     */
    public function listMilestoneIssues(string $owner, string $repo, int $milestoneNumber, array $filters = []): Collection
    {
        $filters['milestone'] = (string) $milestoneNumber;

        return $this->listIssues($owner, $repo, $filters);
    }
}
