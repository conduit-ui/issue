<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Requests\Labels\AddLabelsRequest;
use ConduitUI\Issue\Requests\Labels\RemoveAllLabelsRequest;
use ConduitUI\Issue\Requests\Labels\RemoveLabelRequest;
use ConduitUI\Issue\Requests\Labels\ReplaceAllLabelsRequest;

trait ManagesIssueLabels
{
    public function addLabels(string $owner, string $repo, int $issueNumber, array $labels): Issue
    {
        $response = $this->connector->send(
            new AddLabelsRequest($owner, $repo, $issueNumber, $labels)
        );

        return Issue::fromArray($response->json());
    }

    public function removeLabels(string $owner, string $repo, int $issueNumber, array $labels): Issue
    {
        foreach ($labels as $label) {
            $this->connector->send(
                new RemoveLabelRequest($owner, $repo, $issueNumber, $label)
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
            new ReplaceAllLabelsRequest($owner, $repo, $issueNumber, $labels)
        );

        return Issue::fromArray($response->json());
    }

    public function removeAllLabels(string $owner, string $repo, int $issueNumber): Issue
    {
        $this->connector->send(
            new RemoveAllLabelsRequest($owner, $repo, $issueNumber)
        );

        return $this->getIssue($owner, $repo, $issueNumber);
    }
}
