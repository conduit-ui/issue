<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Data\Label;
use ConduitUI\Issue\Data\User;
use ConduitUI\Issue\Support\IssueCollection;

describe('IssueCollection', function () {
    beforeEach(function () {
        $this->user = new User(
            id: 1,
            login: 'testuser',
            avatarUrl: 'https://example.com/avatar.png',
            htmlUrl: 'https://github.com/testuser',
            type: 'User'
        );

        $this->bugLabel = new Label(
            id: 1,
            name: 'bug',
            color: 'ff0000',
            description: 'Bug report'
        );

        $this->featureLabel = new Label(
            id: 2,
            name: 'feature',
            color: '00ff00',
            description: 'Feature request'
        );

        $this->assignee1 = new User(
            id: 2,
            login: 'dev1',
            avatarUrl: 'https://example.com/dev1.png',
            htmlUrl: 'https://github.com/dev1',
            type: 'User'
        );

        $this->assignee2 = new User(
            id: 3,
            login: 'dev2',
            avatarUrl: 'https://example.com/dev2.png',
            htmlUrl: 'https://github.com/dev2',
            type: 'User'
        );
    });

    describe('Label Filtering', function () {
        it('filters issues with a specific label', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'Bug Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [$this->bugLabel],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user
                ),
                new Issue(
                    id: 2,
                    number: 2,
                    title: 'Feature Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [$this->featureLabel],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/2',
                    user: $this->user
                ),
            ]);

            $bugs = $issues->withLabel('bug');

            expect($bugs)->toHaveCount(1)
                ->and($bugs->first()->number)->toBe(1);
        });

        it('filters issues without a specific label', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'Bug Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [$this->bugLabel],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user
                ),
                new Issue(
                    id: 2,
                    number: 2,
                    title: 'Feature Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [$this->featureLabel],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/2',
                    user: $this->user
                ),
            ]);

            $notBugs = $issues->withoutLabel('bug');

            expect($notBugs)->toHaveCount(1)
                ->and($notBugs->first()->number)->toBe(2);
        });

        it('handles issues with no labels', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'No Labels',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user
                ),
            ]);

            expect($issues->withLabel('bug'))->toHaveCount(0)
                ->and($issues->withoutLabel('bug'))->toHaveCount(1);
        });
    });

    describe('State Filtering', function () {
        it('filters open issues', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'Open Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user
                ),
                new Issue(
                    id: 2,
                    number: 2,
                    title: 'Closed Issue',
                    body: 'Description',
                    state: 'closed',
                    locked: false,
                    assignees: [],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-02'),
                    closedAt: new DateTime('2024-01-02'),
                    htmlUrl: 'https://github.com/owner/repo/issues/2',
                    user: $this->user
                ),
            ]);

            $openIssues = $issues->open();

            expect($openIssues)->toHaveCount(1)
                ->and($openIssues->first()->state)->toBe('open');
        });

        it('filters closed issues', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'Open Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user
                ),
                new Issue(
                    id: 2,
                    number: 2,
                    title: 'Closed Issue',
                    body: 'Description',
                    state: 'closed',
                    locked: false,
                    assignees: [],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-02'),
                    closedAt: new DateTime('2024-01-02'),
                    htmlUrl: 'https://github.com/owner/repo/issues/2',
                    user: $this->user
                ),
            ]);

            $closedIssues = $issues->closed();

            expect($closedIssues)->toHaveCount(1)
                ->and($closedIssues->first()->state)->toBe('closed');
        });
    });

    describe('Assignee Filtering', function () {
        it('filters assigned issues', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'Assigned Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [$this->assignee1],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user,
                    assignee: $this->assignee1
                ),
                new Issue(
                    id: 2,
                    number: 2,
                    title: 'Unassigned Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/2',
                    user: $this->user
                ),
            ]);

            $assigned = $issues->assigned();

            expect($assigned)->toHaveCount(1)
                ->and($assigned->first()->assignees)->toHaveCount(1);
        });

        it('filters unassigned issues', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'Assigned Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [$this->assignee1],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user,
                    assignee: $this->assignee1
                ),
                new Issue(
                    id: 2,
                    number: 2,
                    title: 'Unassigned Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/2',
                    user: $this->user
                ),
            ]);

            $unassigned = $issues->unassigned();

            expect($unassigned)->toHaveCount(1)
                ->and($unassigned->first()->assignees)->toHaveCount(0);
        });
    });

    describe('Grouping', function () {
        it('groups by label', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'Bug Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [$this->bugLabel],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user
                ),
                new Issue(
                    id: 2,
                    number: 2,
                    title: 'Feature Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [$this->featureLabel],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/2',
                    user: $this->user
                ),
                new Issue(
                    id: 3,
                    number: 3,
                    title: 'Another Bug',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [$this->bugLabel],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/3',
                    user: $this->user
                ),
            ]);

            $grouped = $issues->groupByLabel();

            expect($grouped)->toHaveCount(2)
                ->and($grouped->get('bug'))->toHaveCount(2)
                ->and($grouped->get('feature'))->toHaveCount(1);
        });

        it('groups by state', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'Open Issue 1',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user
                ),
                new Issue(
                    id: 2,
                    number: 2,
                    title: 'Closed Issue',
                    body: 'Description',
                    state: 'closed',
                    locked: false,
                    assignees: [],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-02'),
                    closedAt: new DateTime('2024-01-02'),
                    htmlUrl: 'https://github.com/owner/repo/issues/2',
                    user: $this->user
                ),
                new Issue(
                    id: 3,
                    number: 3,
                    title: 'Open Issue 2',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/3',
                    user: $this->user
                ),
            ]);

            $grouped = $issues->groupByState();

            expect($grouped)->toHaveCount(2)
                ->and($grouped->get('open'))->toHaveCount(2)
                ->and($grouped->get('closed'))->toHaveCount(1);
        });

        it('groups by assignee', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'Issue 1',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [$this->assignee1],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user,
                    assignee: $this->assignee1
                ),
                new Issue(
                    id: 2,
                    number: 2,
                    title: 'Issue 2',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [$this->assignee2],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/2',
                    user: $this->user,
                    assignee: $this->assignee2
                ),
                new Issue(
                    id: 3,
                    number: 3,
                    title: 'Issue 3',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/3',
                    user: $this->user
                ),
            ]);

            $grouped = $issues->groupByAssignee();

            expect($grouped)->toHaveCount(3)
                ->and($grouped->get('dev1'))->toHaveCount(1)
                ->and($grouped->get('dev2'))->toHaveCount(1)
                ->and($grouped->get('unassigned'))->toHaveCount(1);
        });

        it('handles multiple assignees per issue', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'Multi-assigned Issue',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [$this->assignee1, $this->assignee2],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user,
                    assignee: $this->assignee1
                ),
            ]);

            $grouped = $issues->groupByAssignee();

            expect($grouped)->toHaveCount(2)
                ->and($grouped->get('dev1'))->toHaveCount(1)
                ->and($grouped->get('dev2'))->toHaveCount(1);
        });
    });

    describe('Statistics', function () {
        it('calculates statistics', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'Open Bug',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [$this->assignee1],
                    labels: [$this->bugLabel],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user,
                    assignee: $this->assignee1
                ),
                new Issue(
                    id: 2,
                    number: 2,
                    title: 'Closed Feature',
                    body: 'Description',
                    state: 'closed',
                    locked: false,
                    assignees: [$this->assignee2],
                    labels: [$this->featureLabel],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-02'),
                    closedAt: new DateTime('2024-01-02'),
                    htmlUrl: 'https://github.com/owner/repo/issues/2',
                    user: $this->user,
                    assignee: $this->assignee2,
                    closedBy: $this->assignee2
                ),
                new Issue(
                    id: 3,
                    number: 3,
                    title: 'Unassigned',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/3',
                    user: $this->user
                ),
            ]);

            $stats = $issues->statistics();

            expect($stats['total'])->toBe(3)
                ->and($stats['open'])->toBe(2)
                ->and($stats['closed'])->toBe(1)
                ->and($stats['assigned'])->toBe(2)
                ->and($stats['unassigned'])->toBe(1)
                ->and($stats['labels'])->toBe(['bug', 'feature'])
                ->and($stats['assignees'])->toBe(['dev1', 'dev2']);
        });

        it('handles empty collection', function () {
            $issues = new IssueCollection([]);

            $stats = $issues->statistics();

            expect($stats['total'])->toBe(0)
                ->and($stats['open'])->toBe(0)
                ->and($stats['closed'])->toBe(0)
                ->and($stats['assigned'])->toBe(0)
                ->and($stats['unassigned'])->toBe(0)
                ->and($stats['labels'])->toBe([])
                ->and($stats['assignees'])->toBe([]);
        });
    });

    describe('Method Chaining', function () {
        it('chains multiple filters', function () {
            $issues = new IssueCollection([
                new Issue(
                    id: 1,
                    number: 1,
                    title: 'Open Bug Assigned',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [$this->assignee1],
                    labels: [$this->bugLabel],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/1',
                    user: $this->user,
                    assignee: $this->assignee1
                ),
                new Issue(
                    id: 2,
                    number: 2,
                    title: 'Open Bug Unassigned',
                    body: 'Description',
                    state: 'open',
                    locked: false,
                    assignees: [],
                    labels: [$this->bugLabel],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-01'),
                    closedAt: null,
                    htmlUrl: 'https://github.com/owner/repo/issues/2',
                    user: $this->user
                ),
                new Issue(
                    id: 3,
                    number: 3,
                    title: 'Closed Bug Assigned',
                    body: 'Description',
                    state: 'closed',
                    locked: false,
                    assignees: [$this->assignee2],
                    labels: [$this->bugLabel],
                    milestone: null,
                    comments: 0,
                    createdAt: new DateTime('2024-01-01'),
                    updatedAt: new DateTime('2024-01-02'),
                    closedAt: new DateTime('2024-01-02'),
                    htmlUrl: 'https://github.com/owner/repo/issues/3',
                    user: $this->user,
                    assignee: $this->assignee2,
                    closedBy: $this->assignee2
                ),
            ]);

            $filtered = $issues
                ->open()
                ->withLabel('bug')
                ->unassigned();

            expect($filtered)->toHaveCount(1)
                ->and($filtered->first()->number)->toBe(2);
        });
    });
});
