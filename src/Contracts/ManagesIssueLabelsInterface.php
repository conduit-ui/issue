<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

use ConduitUI\Issue\Data\Issue;

interface ManagesIssueLabelsInterface
{
    public function addLabels(string $owner, string $repo, int $issueNumber, array $labels): Issue;

    public function removeLabels(string $owner, string $repo, int $issueNumber, array $labels): Issue;

    public function addLabel(string $owner, string $repo, int $issueNumber, string $label): Issue;

    public function removeLabel(string $owner, string $repo, int $issueNumber, string $label): Issue;

    public function replaceAllLabels(string $owner, string $repo, int $issueNumber, array $labels): Issue;

    public function removeAllLabels(string $owner, string $repo, int $issueNumber): Issue;
}
