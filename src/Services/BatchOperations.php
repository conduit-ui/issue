<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Support\IssueCollection;
use Exception;
use Illuminate\Support\Collection;

class BatchOperations
{
    public function __construct(
        protected readonly Connector $connector,
        protected readonly string $owner,
        protected readonly string $repo,
    ) {}

    /**
     * Perform batch operation with progress tracking.
     *
     * @param  callable(mixed): mixed  $operation
     * @param  callable(int, int, mixed): void|null  $progress
     * @return Collection<int, array<string, mixed>>
     */
    public function batch(IssueCollection $issues, callable $operation, ?callable $progress = null): Collection
    {
        $results = new Collection;
        $total = $issues->count();
        $current = 0;

        foreach ($issues as $issue) {
            $current++;

            try {
                $result = $operation($issue);
                $results->push([
                    'issue' => $issue->number,
                    'success' => true,
                    'result' => $result,
                ]);
            } catch (Exception $e) {
                $results->push([
                    'issue' => $issue->number,
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($progress !== null) {
                $progress($current, $total, $issue);
            }
        }

        return $results;
    }

    /**
     * Get a new IssueQuery instance.
     */
    protected function query(): IssueQuery
    {
        return new IssueQuery($this->connector, $this->owner, $this->repo);
    }
}
