<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Requests\Comments\CreateCommentRequest;
use ConduitUI\Issue\Requests\Issues\GetIssueRequest;
use ConduitUI\Issue\Requests\Issues\LockIssueRequest;
use ConduitUI\Issue\Requests\Issues\UnlockIssueRequest;
use ConduitUI\Issue\Requests\Issues\UpdateIssueRequest;
use ConduitUI\Issue\Requests\Labels\AddLabelsRequest;
use ConduitUI\Issue\Requests\Labels\RemoveLabelRequest;
use ConduitUI\Issue\Requests\Labels\ReplaceAllLabelsRequest;

final class IssueInstance
{
    protected ?Issue $issue = null;

    protected string $owner;

    protected string $repo;

    public function __construct(
        protected Connector $connector,
        string $fullName,
        protected int $number,
    ) {
        [$this->owner, $this->repo] = explode('/', $fullName, 2);
    }

    /**
     * Get the issue data (cached)
     */
    public function get(): Issue
    {
        if ($this->issue === null) {
            $this->issue = $this->fetch();
        }

        return $this->issue;
    }

    /**
     * Fetch fresh issue data
     */
    public function fresh(): Issue
    {
        $this->issue = $this->fetch();

        return $this->issue;
    }

    /**
     * Update issue attributes
     */
    public function update(array $attributes): self
    {
        $response = $this->connector->send(
            new UpdateIssueRequest($this->owner, $this->repo, $this->number, $attributes)
        );

        $this->issue = Issue::fromArray($response->json());

        return $this;
    }

    /**
     * Set the title
     */
    public function title(string $title): self
    {
        return $this->update(['title' => $title]);
    }

    /**
     * Set the body
     */
    public function body(string $body): self
    {
        return $this->update(['body' => $body]);
    }

    /**
     * Add labels (merges with existing)
     */
    public function addLabel(string $label): self
    {
        return $this->addLabels([$label]);
    }

    /**
     * Add multiple labels
     */
    public function addLabels(array $labels): self
    {
        $this->connector->send(
            new AddLabelsRequest($this->owner, $this->repo, $this->number, $labels)
        );

        $this->issue = $this->fresh();

        return $this;
    }

    /**
     * Remove a label
     */
    public function removeLabel(string $label): self
    {
        $this->connector->send(
            new RemoveLabelRequest($this->owner, $this->repo, $this->number, $label)
        );

        $this->issue = $this->fresh();

        return $this;
    }

    /**
     * Remove multiple labels
     */
    public function removeLabels(array $labels): self
    {
        foreach ($labels as $label) {
            $this->removeLabel($label);
        }

        return $this;
    }

    /**
     * Replace all labels
     */
    public function setLabels(array $labels): self
    {
        $this->connector->send(
            new ReplaceAllLabelsRequest($this->owner, $this->repo, $this->number, $labels)
        );

        $this->issue = $this->fresh();

        return $this;
    }

    /**
     * Assign to user(s)
     */
    public function assign(string|array $assignees): self
    {
        $assignees = is_array($assignees) ? $assignees : [$assignees];

        return $this->update(['assignees' => $assignees]);
    }

    /**
     * Convenience method - assign to single user
     */
    public function assignTo(string $username): self
    {
        return $this->assign($username);
    }

    /**
     * Remove assignees
     */
    public function unassign(string|array $assignees): self
    {
        $assignees = is_array($assignees) ? $assignees : [$assignees];

        $current = array_map(fn ($user) => $user->login, $this->get()->assignees);
        $remaining = array_diff($current, $assignees);

        return $this->update(['assignees' => array_values($remaining)]);
    }

    /**
     * Set milestone
     */
    public function milestone(?int $milestoneNumber): self
    {
        return $this->update(['milestone' => $milestoneNumber]);
    }

    /**
     * Close the issue
     */
    public function close(?string $reason = null): self
    {
        $params = ['state' => 'closed'];

        if ($reason !== null) {
            $params['state_reason'] = $reason;
        }

        return $this->update($params);
    }

    /**
     * Reopen the issue
     */
    public function reopen(): self
    {
        return $this->update(['state' => 'open']);
    }

    /**
     * Lock the issue
     */
    public function lock(?string $reason = null): self
    {
        $this->connector->send(
            new LockIssueRequest($this->owner, $this->repo, $this->number, $reason)
        );

        $this->issue = $this->fresh();

        return $this;
    }

    /**
     * Unlock the issue
     */
    public function unlock(): self
    {
        $this->connector->send(
            new UnlockIssueRequest($this->owner, $this->repo, $this->number)
        );

        $this->issue = $this->fresh();

        return $this;
    }

    /**
     * Add a comment
     */
    public function comment(string $body): Comment
    {
        $response = $this->connector->send(
            new CreateCommentRequest($this->owner, $this->repo, $this->number, $body)
        );

        return Comment::fromArray($response->json());
    }

    /**
     * Fetch issue from API
     */
    protected function fetch(): Issue
    {
        $response = $this->connector->send(
            new GetIssueRequest($this->owner, $this->repo, $this->number)
        );

        return Issue::fromArray($response->json());
    }

    /**
     * Magic method to access issue properties
     */
    public function __get(string $name): mixed
    {
        return $this->get()->$name;
    }
}
