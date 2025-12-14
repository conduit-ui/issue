<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Contracts;

interface IssuesServiceInterface extends ManagesIssueAssigneesInterface, ManagesIssueCommentsInterface, ManagesIssueLabelsInterface, ManagesIssuesInterface {}
