<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Issue;
use Illuminate\Support\Collection;

trait ManagesIssues
{
    /**
     * @return Collection<int, Issue>
     */
    public function listIssues(string $owner, string $repo, array $filters = []): Collection
    {
        $response = $this->connector->send(
            $this->connector->get("/repos/{$owner}/{$repo}/issues", $filters)
        );

        /** @var array<int, array<string, mixed>> $data */
        $data = $response->json();

        return collect($data)
            ->map(fn (array $item): Issue => Issue::fromArray($item));
    }

    public function getIssue(string $owner, string $repo, int $issueNumber): Issue
    {
        $response = $this->connector->send(
            $this->connector->get("/repos/{$owner}/{$repo}/issues/{$issueNumber}")
        );

        /** @var array<string, mixed> $data */
        $data = $response->json();

        return Issue::fromArray($data);
    }

    public function createIssue(string $owner, string $repo, array $data): Issue
    {
        $response = $this->connector->send(
            $this->connector->post("/repos/{$owner}/{$repo}/issues", $data)
        );

        /** @var array<string, mixed> $responseData */
        $responseData = $response->json();

        return Issue::fromArray($responseData);
    }

    public function updateIssue(string $owner, string $repo, int $issueNumber, array $data): Issue
    {
        $response = $this->connector->send(
            $this->connector->patch("/repos/{$owner}/{$repo}/issues/{$issueNumber}", $data)
        );

        /** @var array<string, mixed> $responseData */
        $responseData = $response->json();

        return Issue::fromArray($responseData);
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
