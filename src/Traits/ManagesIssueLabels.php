<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Issue;

trait ManagesIssueLabels
{
    public function addLabels(string $owner, string $repo, int $issueNumber, array $labels): Issue
    {
        $response = $this->connector->send(
            $this->connector->post("/repos/{$owner}/{$repo}/issues/{$issueNumber}/labels", [
                'labels' => $labels,
            ])
        );

        return Issue::fromArray($response->json());
    }

    public function removeLabels(string $owner, string $repo, int $issueNumber, array $labels): Issue
    {
        foreach ($labels as $label) {
            $this->connector->send(
                $this->connector->delete("/repos/{$owner}/{$repo}/issues/{$issueNumber}/labels/{$label}")
            );
        }

        return $this->getIssue($owner, $repo, $issueNumber);
    }

    public function addLabel(string $owner, string $repo, int $issueNumber, string $label): Issue
    {
        return $this->addLabels($owner, $repo, $issueNumber, [$label]);
    }

    public function removeLabel(string $owner, string $repo, int $issueNumber, string $label): Issue
    {
        return $this->removeLabels($owner, $repo, $issueNumber, [$label]);
    }

    public function replaceAllLabels(string $owner, string $repo, int $issueNumber, array $labels): Issue
    {
        $response = $this->connector->send(
            $this->connector->put("/repos/{$owner}/{$repo}/issues/{$issueNumber}/labels", [
                'labels' => $labels,
            ])
        );

        return Issue::fromArray($response->json());
    }

    public function removeAllLabels(string $owner, string $repo, int $issueNumber): Issue
    {
        $this->connector->send(
            $this->connector->delete("/repos/{$owner}/{$repo}/issues/{$issueNumber}/labels")
        );

        return $this->getIssue($owner, $repo, $issueNumber);
    }
}
