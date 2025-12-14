<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Data\Label;
use ConduitUI\Issue\Data\User;

describe('Issue', function () {
    it('can create issue from array', function () {
        $data = [
            'id' => 123,
            'number' => 1,
            'title' => 'Test Issue',
            'body' => 'This is a test issue',
            'state' => 'open',
            'locked' => false,
            'assignees' => [
                [
                    'id' => 456,
                    'login' => 'testuser',
                    'avatar_url' => 'https://github.com/testuser.png',
                    'html_url' => 'https://github.com/testuser',
                    'type' => 'User',
                ],
            ],
            'labels' => [
                [
                    'id' => 789,
                    'name' => 'bug',
                    'color' => 'fc2929',
                    'description' => 'Something is broken',
                ],
            ],
            'milestone' => [
                'title' => 'v1.0',
            ],
            'comments' => 5,
            'created_at' => '2023-01-01T12:00:00Z',
            'updated_at' => '2023-01-02T12:00:00Z',
            'closed_at' => null,
            'html_url' => 'https://github.com/owner/repo/issues/1',
            'user' => [
                'id' => 101,
                'login' => 'author',
                'avatar_url' => 'https://github.com/author.png',
                'html_url' => 'https://github.com/author',
                'type' => 'User',
            ],
            'assignee' => [
                'id' => 456,
                'login' => 'testuser',
                'avatar_url' => 'https://github.com/testuser.png',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'closed_by' => null,
        ];

        $issue = Issue::fromArray($data);

        expect($issue->id)->toBe(123);
        expect($issue->number)->toBe(1);
        expect($issue->title)->toBe('Test Issue');
        expect($issue->body)->toBe('This is a test issue');
        expect($issue->state)->toBe('open');
        expect($issue->locked)->toBeFalse();
        expect($issue->assignees)->toHaveCount(1);
        expect($issue->assignees[0])->toBeInstanceOf(User::class);
        expect($issue->labels)->toHaveCount(1);
        expect($issue->labels[0])->toBeInstanceOf(Label::class);
        expect($issue->milestone)->toBe('v1.0');
        expect($issue->comments)->toBe(5);
        expect($issue->user)->toBeInstanceOf(User::class);
        expect($issue->assignee)->toBeInstanceOf(User::class);
        expect($issue->closedBy)->toBeNull();
    });

    it('can convert issue to array', function () {
        $user = new User(101, 'author', 'https://github.com/author.png', 'https://github.com/author', 'User');
        $assignee = new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User');
        $label = new Label(789, 'bug', 'fc2929', 'Something is broken');

        $issue = new Issue(
            id: 123,
            number: 1,
            title: 'Test Issue',
            body: 'This is a test issue',
            state: 'open',
            locked: false,
            assignees: [$assignee],
            labels: [$label],
            milestone: 'v1.0',
            comments: 5,
            createdAt: new DateTime('2023-01-01T12:00:00Z'),
            updatedAt: new DateTime('2023-01-02T12:00:00Z'),
            closedAt: null,
            htmlUrl: 'https://github.com/owner/repo/issues/1',
            user: $user,
            assignee: $assignee,
            closedBy: null,
        );

        $array = $issue->toArray();

        expect($array['id'])->toBe(123);
        expect($array['number'])->toBe(1);
        expect($array['title'])->toBe('Test Issue');
        expect($array['state'])->toBe('open');
        expect($array['assignees'])->toHaveCount(1);
        expect($array['labels'])->toHaveCount(1);
        expect($array['milestone'])->toBe('v1.0');
        expect($array['closed_at'])->toBeNull();
    });

    it('can check if issue is open', function () {
        $issue = new Issue(
            id: 123,
            number: 1,
            title: 'Test Issue',
            body: 'This is a test issue',
            state: 'open',
            locked: false,
            assignees: [],
            labels: [],
            milestone: null,
            comments: 0,
            createdAt: new DateTime,
            updatedAt: new DateTime,
            closedAt: null,
            htmlUrl: 'https://github.com/owner/repo/issues/1',
            user: new User(101, 'author', 'https://github.com/author.png', 'https://github.com/author', 'User'),
        );

        expect($issue->isOpen())->toBeTrue();
        expect($issue->isClosed())->toBeFalse();
    });

    it('can check if issue is closed', function () {
        $issue = new Issue(
            id: 123,
            number: 1,
            title: 'Test Issue',
            body: 'This is a test issue',
            state: 'closed',
            locked: false,
            assignees: [],
            labels: [],
            milestone: null,
            comments: 0,
            createdAt: new DateTime,
            updatedAt: new DateTime,
            closedAt: new DateTime,
            htmlUrl: 'https://github.com/owner/repo/issues/1',
            user: new User(101, 'author', 'https://github.com/author.png', 'https://github.com/author', 'User'),
        );

        expect($issue->isOpen())->toBeFalse();
        expect($issue->isClosed())->toBeTrue();
    });
});
