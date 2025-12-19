<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Contracts\IssuesServiceInterface;
use ConduitUI\Issue\Traits\ManagesCommentReactions;
use ConduitUI\Issue\Traits\ManagesIssueAssignees;
use ConduitUI\Issue\Traits\ManagesIssueComments;
use ConduitUI\Issue\Traits\ManagesIssueEvents;
use ConduitUI\Issue\Traits\ManagesIssueLabels;
use ConduitUI\Issue\Traits\ManagesIssues;
use ConduitUI\Issue\Traits\ManagesMilestones;

class IssuesService implements IssuesServiceInterface
{
    use ManagesCommentReactions;
    use ManagesIssueAssignees;
    use ManagesIssueComments;
    use ManagesIssueEvents;
    use ManagesIssueLabels;
    use ManagesIssues;
    use ManagesMilestones;

    public function __construct(
        private readonly Connector $connector
    ) {}

    /**
     * Find and interact with a specific issue
     */
    public function find(string $fullName, int $number): IssueInstance
    {
        return new IssueInstance($this->connector, $fullName, $number);
    }
}
