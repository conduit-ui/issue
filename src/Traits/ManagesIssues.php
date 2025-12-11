<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Issue;
use Illuminate\Support\Collection;

trait ManagesIssues
{
    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\GithubIssues\Data\Issue>
     */
    public function listIssues(string $owner, string $repo, array $filters = []): Collection
    {
        $response = $this->connector->send(
            $this->connector->get("/repos/{$owner}/{$repo}/issues", $filters)
        );

        return collect($response->json())
            ->map(fn (array $data) => Issue::fromArray($data));
    }

    public function getIssue(string $owner, string $repo, int $issueNumber): Issue
    {
        $response = $this->connector->send(
            $this->connector->get("/repos/{$owner}/{$repo}/issues/{$issueNumber}")
        );

        return Issue::fromArray($response->json());
    }

    public function createIssue(string $owner, string $repo, array $data): Issue
    {
        $response = $this->connector->send(
            $this->connector->post("/repos/{$owner}/{$repo}/issues", $data)
        );

        return Issue::fromArray($response->json());
    }

    public function updateIssue(string $owner, string $repo, int $issueNumber, array $data): Issue
    {
        $response = $this->connector->send(
            $this->connector->patch("/repos/{$owner}/{$repo}/issues/{$issueNumber}", $data)
        );

        return Issue::fromArray($response->json());
    }

    public function closeIssue(string $owner, string $repo, int $issueNumber): Issue
    {
        return $this->updateIssue($owner, $repo, $issueNumber, ['state' => 'closed']);
    }

    public function reopenIssue(string $owner, string $repo, int $issueNumber): Issue
    {
        return $this->updateIssue($owner, $repo, $issueNumber, ['state' => 'open']);
    }
}
