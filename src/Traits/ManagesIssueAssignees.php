<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Requests\Assignees\AddAssigneesRequest;
use ConduitUI\Issue\Requests\Assignees\RemoveAssigneesRequest;

trait ManagesIssueAssignees
{
    public function addAssignees(string $owner, string $repo, int $issueNumber, array $assignees): Issue
    {
        $response = $this->connector->send(
            new AddAssigneesRequest($owner, $repo, $issueNumber, $assignees)
        );

        return Issue::fromArray($response->json());
    }

    public function removeAssignees(string $owner, string $repo, int $issueNumber, array $assignees): Issue
    {
        $response = $this->connector->send(
            new RemoveAssigneesRequest($owner, $repo, $issueNumber, $assignees)
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
