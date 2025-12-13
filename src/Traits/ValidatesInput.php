<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use InvalidArgumentException;

trait ValidatesInput
{
    protected function validateRepository(string $owner, string $repo): void
    {
        $this->validateOwner($owner);
        $this->validateRepoName($repo);
    }

    protected function validateOwner(string $owner): void
    {
        if (trim($owner) === '') {
            throw new InvalidArgumentException('Owner cannot be empty');
        }

        if (strlen($owner) > 39) {
            throw new InvalidArgumentException('Owner name cannot exceed 39 characters');
        }

        if (! preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?$/', $owner)) {
            throw new InvalidArgumentException('Owner name contains invalid characters');
        }

        if (str_contains($owner, '--')) {
            throw new InvalidArgumentException('Owner name cannot contain consecutive hyphens');
        }
    }

    protected function validateRepoName(string $repo): void
    {
        if (trim($repo) === '') {
            throw new InvalidArgumentException('Repository name cannot be empty');
        }

        if (strlen($repo) > 100) {
            throw new InvalidArgumentException('Repository name cannot exceed 100 characters');
        }

        $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '', $repo);

        if ($sanitized !== $repo) {
            throw new InvalidArgumentException('Repository name contains invalid characters');
        }
    }

    protected function validateIssueNumber(int $issueNumber): void
    {
        if ($issueNumber < 1) {
            throw new InvalidArgumentException('Issue number must be positive');
        }
    }

    protected function validateIssueData(array $data): array
    {
        $sanitized = [];

        if (isset($data['title'])) {
            $sanitized['title'] = $this->sanitizeString($data['title'], 'title', 256);
        }

        if (isset($data['body'])) {
            $sanitized['body'] = $this->sanitizeString($data['body'], 'body', 65536);
        }

        if (isset($data['state'])) {
            $this->validateState($data['state']);
            $sanitized['state'] = $data['state'];
        }

        if (isset($data['state_reason'])) {
            $this->validateStateReason($data['state_reason']);
            $sanitized['state_reason'] = $data['state_reason'];
        }

        if (isset($data['labels'])) {
            $sanitized['labels'] = $this->validateLabels($data['labels']);
        }

        if (isset($data['assignees'])) {
            $sanitized['assignees'] = $this->validateAssignees($data['assignees']);
        }

        if (isset($data['milestone'])) {
            $sanitized['milestone'] = $this->validateMilestone($data['milestone']);
        }

        return $sanitized;
    }

    private function sanitizeString(mixed $value, string $field, int $maxLength): string
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException("{$field} must be a string");
        }

        $trimmed = trim($value);

        if (strlen($trimmed) > $maxLength) {
            throw new InvalidArgumentException("{$field} cannot exceed {$maxLength} characters");
        }

        return $trimmed;
    }

    private function validateState(mixed $state): void
    {
        if (! is_string($state)) {
            throw new InvalidArgumentException('State must be a string');
        }

        $validStates = ['open', 'closed'];

        if (! in_array($state, $validStates, true)) {
            throw new InvalidArgumentException('State must be one of: open, closed');
        }
    }

    private function validateStateReason(mixed $reason): void
    {
        if (! is_string($reason)) {
            throw new InvalidArgumentException('State reason must be a string');
        }

        $validReasons = ['completed', 'not_planned', 'reopened'];

        if (! in_array($reason, $validReasons, true)) {
            throw new InvalidArgumentException('State reason must be one of: completed, not_planned, reopened');
        }
    }

    private function validateLabels(mixed $labels): array
    {
        if (! is_array($labels)) {
            throw new InvalidArgumentException('Labels must be an array');
        }

        foreach ($labels as $label) {
            if (! is_string($label)) {
                throw new InvalidArgumentException('Each label must be a string');
            }

            if (trim($label) === '') {
                throw new InvalidArgumentException('Label cannot be empty');
            }
        }

        return array_values(array_map('trim', $labels));
    }

    private function validateAssignees(mixed $assignees): array
    {
        if (! is_array($assignees)) {
            throw new InvalidArgumentException('Assignees must be an array');
        }

        foreach ($assignees as $assignee) {
            if (! is_string($assignee)) {
                throw new InvalidArgumentException('Each assignee must be a string');
            }

            if (trim($assignee) === '') {
                throw new InvalidArgumentException('Assignee cannot be empty');
            }
        }

        return array_values(array_map('trim', $assignees));
    }

    private function validateMilestone(mixed $milestone): ?int
    {
        if ($milestone === null) {
            return null;
        }

        if (! is_int($milestone)) {
            throw new InvalidArgumentException('Milestone must be an integer or null');
        }

        if ($milestone < 1) {
            throw new InvalidArgumentException('Milestone must be positive');
        }

        return $milestone;
    }
}
