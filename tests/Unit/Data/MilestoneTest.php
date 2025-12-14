<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Milestone;
use ConduitUI\Issue\Data\User;

describe('Milestone', function () {
    it('can create milestone from array', function () {
        $data = [
            'id' => 123,
            'number' => 1,
            'title' => 'v1.0.0',
            'description' => 'First major release',
            'state' => 'open',
            'creator' => [
                'id' => 456,
                'login' => 'testuser',
                'avatar_url' => 'https://github.com/testuser.png',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'open_issues' => 5,
            'closed_issues' => 10,
            'due_on' => '2024-12-31T23:59:59Z',
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-06-01T00:00:00Z',
            'closed_at' => null,
            'html_url' => 'https://github.com/owner/repo/milestone/1',
        ];

        $milestone = Milestone::fromArray($data);

        expect($milestone->id)->toBe(123);
        expect($milestone->number)->toBe(1);
        expect($milestone->title)->toBe('v1.0.0');
        expect($milestone->description)->toBe('First major release');
        expect($milestone->state)->toBe('open');
        expect($milestone->creator)->toBeInstanceOf(User::class);
        expect($milestone->creator->login)->toBe('testuser');
        expect($milestone->openIssues)->toBe(5);
        expect($milestone->closedIssues)->toBe(10);
        expect($milestone->dueOn)->toBeInstanceOf(DateTime::class);
        expect($milestone->dueOn->format('Y-m-d'))->toBe('2024-12-31');
        expect($milestone->createdAt)->toBeInstanceOf(DateTime::class);
        expect($milestone->updatedAt)->toBeInstanceOf(DateTime::class);
        expect($milestone->closedAt)->toBeNull();
        expect($milestone->htmlUrl)->toBe('https://github.com/owner/repo/milestone/1');
    });

    it('can create milestone from array with null values', function () {
        $data = [
            'id' => 123,
            'number' => 1,
            'title' => 'v1.0.0',
            'description' => null,
            'state' => 'closed',
            'creator' => [
                'id' => 456,
                'login' => 'testuser',
                'avatar_url' => 'https://github.com/testuser.png',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'open_issues' => 0,
            'closed_issues' => 15,
            'due_on' => null,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-06-01T00:00:00Z',
            'closed_at' => '2024-06-01T00:00:00Z',
            'html_url' => 'https://github.com/owner/repo/milestone/1',
        ];

        $milestone = Milestone::fromArray($data);

        expect($milestone->description)->toBeNull();
        expect($milestone->dueOn)->toBeNull();
        expect($milestone->closedAt)->toBeInstanceOf(DateTime::class);
    });

    it('can convert milestone to array', function () {
        $creator = new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User');

        $milestone = new Milestone(
            id: 123,
            number: 1,
            title: 'v1.0.0',
            description: 'First major release',
            state: 'open',
            creator: $creator,
            openIssues: 5,
            closedIssues: 10,
            dueOn: new DateTime('2024-12-31T23:59:59Z'),
            createdAt: new DateTime('2024-01-01T00:00:00Z'),
            updatedAt: new DateTime('2024-06-01T00:00:00Z'),
            closedAt: null,
            htmlUrl: 'https://github.com/owner/repo/milestone/1',
        );

        $array = $milestone->toArray();

        expect($array['id'])->toBe(123);
        expect($array['number'])->toBe(1);
        expect($array['title'])->toBe('v1.0.0');
        expect($array['description'])->toBe('First major release');
        expect($array['state'])->toBe('open');
        expect($array['creator'])->toBeArray();
        expect($array['creator']['login'])->toBe('testuser');
        expect($array['open_issues'])->toBe(5);
        expect($array['closed_issues'])->toBe(10);
        expect($array['due_on'])->toBeString();
        expect($array['created_at'])->toBeString();
        expect($array['updated_at'])->toBeString();
        expect($array['closed_at'])->toBeNull();
        expect($array['html_url'])->toBe('https://github.com/owner/repo/milestone/1');
    });

    it('can check if milestone is open', function () {
        $milestone = new Milestone(
            id: 123,
            number: 1,
            title: 'v1.0.0',
            description: 'Release',
            state: 'open',
            creator: new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User'),
            openIssues: 5,
            closedIssues: 0,
            dueOn: null,
            createdAt: new DateTime,
            updatedAt: new DateTime,
            closedAt: null,
            htmlUrl: 'https://github.com/owner/repo/milestone/1',
        );

        expect($milestone->isOpen())->toBeTrue();
        expect($milestone->isClosed())->toBeFalse();
    });

    it('can check if milestone is closed', function () {
        $milestone = new Milestone(
            id: 123,
            number: 1,
            title: 'v1.0.0',
            description: 'Release',
            state: 'closed',
            creator: new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User'),
            openIssues: 0,
            closedIssues: 5,
            dueOn: null,
            createdAt: new DateTime,
            updatedAt: new DateTime,
            closedAt: new DateTime,
            htmlUrl: 'https://github.com/owner/repo/milestone/1',
        );

        expect($milestone->isOpen())->toBeFalse();
        expect($milestone->isClosed())->toBeTrue();
    });

    it('can check if milestone is overdue', function () {
        $pastDate = new DateTime('-1 day');
        $futureDate = new DateTime('+1 day');

        $overdueMilestone = new Milestone(
            id: 123,
            number: 1,
            title: 'v1.0.0',
            description: 'Release',
            state: 'open',
            creator: new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User'),
            openIssues: 5,
            closedIssues: 0,
            dueOn: $pastDate,
            createdAt: new DateTime,
            updatedAt: new DateTime,
            closedAt: null,
            htmlUrl: 'https://github.com/owner/repo/milestone/1',
        );

        $onTimeMilestone = new Milestone(
            id: 124,
            number: 2,
            title: 'v2.0.0',
            description: 'Release',
            state: 'open',
            creator: new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User'),
            openIssues: 5,
            closedIssues: 0,
            dueOn: $futureDate,
            createdAt: new DateTime,
            updatedAt: new DateTime,
            closedAt: null,
            htmlUrl: 'https://github.com/owner/repo/milestone/2',
        );

        expect($overdueMilestone->isOverdue())->toBeTrue();
        expect($onTimeMilestone->isOverdue())->toBeFalse();
    });

    it('returns false for overdue when milestone has no due date', function () {
        $milestone = new Milestone(
            id: 123,
            number: 1,
            title: 'v1.0.0',
            description: 'Release',
            state: 'open',
            creator: new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User'),
            openIssues: 5,
            closedIssues: 0,
            dueOn: null,
            createdAt: new DateTime,
            updatedAt: new DateTime,
            closedAt: null,
            htmlUrl: 'https://github.com/owner/repo/milestone/1',
        );

        expect($milestone->isOverdue())->toBeFalse();
    });

    it('returns false for overdue when milestone is closed even if past due date', function () {
        $pastDate = new DateTime('-1 day');

        $milestone = new Milestone(
            id: 123,
            number: 1,
            title: 'v1.0.0',
            description: 'Release',
            state: 'closed',
            creator: new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User'),
            openIssues: 0,
            closedIssues: 5,
            dueOn: $pastDate,
            createdAt: new DateTime,
            updatedAt: new DateTime,
            closedAt: new DateTime,
            htmlUrl: 'https://github.com/owner/repo/milestone/1',
        );

        expect($milestone->isOverdue())->toBeFalse();
    });

    it('can calculate completion percentage', function () {
        $milestone = new Milestone(
            id: 123,
            number: 1,
            title: 'v1.0.0',
            description: 'Release',
            state: 'open',
            creator: new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User'),
            openIssues: 3,
            closedIssues: 7,
            dueOn: null,
            createdAt: new DateTime,
            updatedAt: new DateTime,
            closedAt: null,
            htmlUrl: 'https://github.com/owner/repo/milestone/1',
        );

        expect($milestone->completionPercentage())->toBe(70.0);
    });

    it('returns zero completion percentage when no issues exist', function () {
        $milestone = new Milestone(
            id: 123,
            number: 1,
            title: 'v1.0.0',
            description: 'Release',
            state: 'open',
            creator: new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User'),
            openIssues: 0,
            closedIssues: 0,
            dueOn: null,
            createdAt: new DateTime,
            updatedAt: new DateTime,
            closedAt: null,
            htmlUrl: 'https://github.com/owner/repo/milestone/1',
        );

        expect($milestone->completionPercentage())->toBe(0.0);
    });

    it('returns 100 completion percentage when all issues are closed', function () {
        $milestone = new Milestone(
            id: 123,
            number: 1,
            title: 'v1.0.0',
            description: 'Release',
            state: 'closed',
            creator: new User(456, 'testuser', 'https://github.com/testuser.png', 'https://github.com/testuser', 'User'),
            openIssues: 0,
            closedIssues: 10,
            dueOn: null,
            createdAt: new DateTime,
            updatedAt: new DateTime,
            closedAt: new DateTime,
            htmlUrl: 'https://github.com/owner/repo/milestone/1',
        );

        expect($milestone->completionPercentage())->toBe(100.0);
    });
});
