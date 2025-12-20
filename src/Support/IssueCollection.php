<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Support;

use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Data\Label;
use Illuminate\Support\Collection;

/**
 * @extends Collection<int, Issue>
 */
class IssueCollection extends Collection
{
    /**
     * Filter issues by label.
     */
    public function withLabel(string $label): self
    {
        return $this->filter(function (Issue $issue) use ($label): bool {
            foreach ($issue->labels as $issueLabel) {
                if ($issueLabel->name === $label) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Filter issues without label.
     */
    public function withoutLabel(string $label): self
    {
        return $this->reject(function (Issue $issue) use ($label): bool {
            foreach ($issue->labels as $issueLabel) {
                if ($issueLabel->name === $label) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Filter open issues.
     */
    public function open(): self
    {
        return $this->filter(fn (Issue $issue): bool => $issue->isOpen());
    }

    /**
     * Filter closed issues.
     */
    public function closed(): self
    {
        return $this->filter(fn (Issue $issue): bool => $issue->isClosed());
    }

    /**
     * Filter assigned issues.
     */
    public function assigned(): self
    {
        return $this->filter(fn (Issue $issue): bool => count($issue->assignees) > 0);
    }

    /**
     * Filter unassigned issues.
     */
    public function unassigned(): self
    {
        return $this->filter(fn (Issue $issue): bool => count($issue->assignees) === 0);
    }

    /**
     * Group by label.
     *
     * @return Collection<string, IssueCollection>
     */
    public function groupByLabel(): Collection
    {
        $grouped = new Collection;

        foreach ($this as $issue) {
            foreach ($issue->labels as $label) {
                if (! $grouped->has($label->name)) {
                    $grouped->put($label->name, new static);
                }
                $grouped->get($label->name)->push($issue);
            }
        }

        return $grouped;
    }

    /**
     * Group by state.
     *
     * @return Collection<string, IssueCollection>
     */
    public function groupByState(): Collection
    {
        return $this->groupBy('state')->map(fn ($items) => new static($items));
    }

    /**
     * Group by assignee.
     *
     * @return Collection<string, IssueCollection>
     */
    public function groupByAssignee(): Collection
    {
        $grouped = new Collection;

        foreach ($this as $issue) {
            if (count($issue->assignees) === 0) {
                if (! $grouped->has('unassigned')) {
                    $grouped->put('unassigned', new static);
                }
                $grouped->get('unassigned')->push($issue);
            } else {
                foreach ($issue->assignees as $assignee) {
                    if (! $grouped->has($assignee->login)) {
                        $grouped->put($assignee->login, new static);
                    }
                    $grouped->get($assignee->login)->push($issue);
                }
            }
        }

        return $grouped;
    }

    /**
     * Get statistics.
     *
     * @return array<string, mixed>
     */
    public function statistics(): array
    {
        $labels = collect();
        $assignees = collect();

        foreach ($this as $issue) {
            foreach ($issue->labels as $label) {
                $labels->push($label->name);
            }

            foreach ($issue->assignees as $assignee) {
                $assignees->push($assignee->login);
            }
        }

        return [
            'total' => $this->count(),
            'open' => $this->open()->count(),
            'closed' => $this->closed()->count(),
            'assigned' => $this->assigned()->count(),
            'unassigned' => $this->unassigned()->count(),
            'labels' => $labels->unique()->sort()->values()->all(),
            'assignees' => $assignees->unique()->sort()->values()->all(),
        ];
    }

    /**
     * Check if an issue has a specific label.
     */
    protected function hasLabel(Issue $issue, string $label): bool
    {
        foreach ($issue->labels as $issueLabel) {
            if ($issueLabel->name === $label) {
                return true;
            }
        }

        return false;
    }
}
