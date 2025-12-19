<?php

declare(strict_types=1);

if (! function_exists('fullIssueResponse')) {
    function fullIssueResponse(array $overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'number' => 123,
            'title' => 'Test Issue',
            'body' => 'Description',
            'state' => 'open',
            'locked' => false,
            'comments' => 0,
            'user' => ['id' => 1, 'login' => 'user', 'avatar_url' => 'https://example.com/avatar.png', 'html_url' => 'https://github.com/user', 'type' => 'User'],
            'labels' => [],
            'assignees' => [],
            'assignee' => null,
            'milestone' => null,
            'closed_at' => null,
            'closed_by' => null,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'html_url' => 'https://github.com/owner/repo/issues/123',
        ], $overrides);
    }
}
