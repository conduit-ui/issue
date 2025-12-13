<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Contracts\IssuesServiceInterface;
use ConduitUI\Issue\Traits\ManagesIssueAssignees;
use ConduitUI\Issue\Traits\ManagesIssueLabels;
use ConduitUI\Issue\Traits\ManagesIssues;

class IssuesService implements IssuesServiceInterface
{
    use ManagesIssueAssignees;
    use ManagesIssueLabels;
    use ManagesIssues;

    public function __construct(
        private readonly Connector $connector
    ) {}

    /**
     * Get the underlying connector for advanced operations.
     */
    public function connector(): Connector
    {
        return $this->connector;
    }
}
