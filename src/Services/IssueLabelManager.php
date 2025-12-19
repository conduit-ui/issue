<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Label;
use ConduitUI\Issue\Requests\IssueLabels\AddIssueLabelsRequest;
use ConduitUI\Issue\Requests\IssueLabels\ClearIssueLabelsRequest;
use ConduitUI\Issue\Requests\IssueLabels\ListIssueLabelsRequest;
use ConduitUI\Issue\Requests\IssueLabels\RemoveIssueLabelRequest;
use ConduitUI\Issue\Requests\IssueLabels\SetIssueLabelsRequest;
use Illuminate\Support\Collection;

final class IssueLabelManager
{
    public function __construct(
        protected Connector $connector,
        protected string $fullName,
        protected int $issueNumber,
    ) {}

    /**
     * Get all labels for this issue
     *
     * @return Collection<int, Label>
     */
    public function all(): Collection
    {
        $response = $this->connector->send(
            new ListIssueLabelsRequest($this->fullName, $this->issueNumber)
        );

        return collect($response->json())
            ->map(fn (mixed $label): Label => Label::fromArray((array) $label));
    }

    /**
     * Add labels to issue
     *
     * @param  string|array<int, string>  $labels
     * @return Collection<int, Label>
     */
    public function add(string|array $labels): Collection
    {
        $labels = is_array($labels) ? $labels : [$labels];

        $response = $this->connector->send(
            new AddIssueLabelsRequest($this->fullName, $this->issueNumber, $labels)
        );

        return collect($response->json())
            ->map(fn (mixed $label): Label => Label::fromArray((array) $label));
    }

    /**
     * Remove a label from issue
     */
    public function remove(string $label): bool
    {
        $response = $this->connector->send(
            new RemoveIssueLabelRequest($this->fullName, $this->issueNumber, $label)
        );

        return $response->successful();
    }

    /**
     * Replace all labels
     *
     * @param  array<int, string>  $labels
     * @return Collection<int, Label>
     */
    public function set(array $labels): Collection
    {
        $response = $this->connector->send(
            new SetIssueLabelsRequest($this->fullName, $this->issueNumber, $labels)
        );

        return collect($response->json())
            ->map(fn (mixed $label): Label => Label::fromArray((array) $label));
    }

    /**
     * Remove all labels
     */
    public function clear(): bool
    {
        $response = $this->connector->send(
            new ClearIssueLabelsRequest($this->fullName, $this->issueNumber)
        );

        return $response->successful();
    }
}
