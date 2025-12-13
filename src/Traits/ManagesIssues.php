<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Requests\Issues\CreateIssueRequest;
use ConduitUI\Issue\Requests\Issues\GetIssueRequest;
use ConduitUI\Issue\Requests\Issues\ListIssuesRequest;
use ConduitUI\Issue\Requests\Issues\UpdateIssueRequest;
use Illuminate\Support\Collection;

trait ManagesIssues
{
    use HandlesApiErrors;
    use ValidatesInput;

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Issue>
     */
    public function listIssues(string $owner, string $repo, array $filters = []): Collection
    {
        $this->validateRepository($owner, $repo);

        $response = $this->connector->send(
            new ListIssuesRequest($owner, $repo, $filters)
        );

        $this->handleApiResponse($response, $owner, $repo);

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return collect($items)
            ->map(fn (array $data): Issue => Issue::fromArray($data));
    }

    public function getIssue(string $owner, string $repo, int $issueNumber): Issue
    {
        $this->validateRepository($owner, $repo);
        $this->validateIssueNumber($issueNumber);

        $response = $this->connector->send(
            new GetIssueRequest($owner, $repo, $issueNumber)
        );

        $this->handleApiResponse($response, $owner, $repo, $issueNumber);

        return Issue::fromArray($response->json());
    }

    public function createIssue(string $owner, string $repo, array $data): Issue
    {
        $this->validateRepository($owner, $repo);
        $sanitizedData = $this->validateIssueData($data);

        $response = $this->connector->send(
            new CreateIssueRequest($owner, $repo, $sanitizedData)
        );

        $this->handleApiResponse($response, $owner, $repo);

        return Issue::fromArray($response->json());
    }

    public function updateIssue(string $owner, string $repo, int $issueNumber, array $data): Issue
    {
        $this->validateRepository($owner, $repo);
        $this->validateIssueNumber($issueNumber);
        $sanitizedData = $this->validateIssueData($data);

        $response = $this->connector->send(
            new UpdateIssueRequest($owner, $repo, $issueNumber, $sanitizedData)
        );

        $this->handleApiResponse($response, $owner, $repo, $issueNumber);

        return Issue::fromArray($response->json());
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
