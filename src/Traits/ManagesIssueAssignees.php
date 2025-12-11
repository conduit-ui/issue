<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Issue;

trait ManagesIssueAssignees
{
    public function addAssignees(string $owner, string $repo, int $issueNumber, array $assignees): Issue
    {
        $response = $this->connector->send(
            $this->connector->post("/repos/{$owner}/{$repo}/issues/{$issueNumber}/assignees", [
                'assignees' => $assignees,
            ])
        );

        return Issue::fromArray($response->json());
    }

    public function removeAssignees(string $owner, string $repo, int $issueNumber, array $assignees): Issue
    {
        $response = $this->connector->send(
            $this->connector->delete("/repos/{$owner}/{$repo}/issues/{$issueNumber}/assignees", [
                'assignees' => $assignees,
            ])
        );

        return Issue::fromArray($response->json());
    }

    public function assignIssue(string $owner, string $repo, int $issueNumber, string $assignee): Issue
    {
        return $this->addAssignees($owner, $repo, $issueNumber, [$assignee]);
    }

    public function unassignIssue(string $owner, string $repo, int $issueNumber, string $assignee): Issue
    {
        return $this->removeAssignees($owner, $repo, $issueNumber, [$assignee]);
    }
}
