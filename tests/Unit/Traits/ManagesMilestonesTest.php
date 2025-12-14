<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Milestone;
use ConduitUI\Issue\Services\IssuesService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function fullMilestoneResponse(array $overrides = []): array
{
    return array_merge([
        'id' => 1,
        'number' => 1,
        'title' => 'v1.0.0',
        'description' => 'First release',
        'state' => 'open',
        'creator' => [
            'id' => 1,
            'login' => 'user',
            'avatar_url' => 'https://example.com/avatar.png',
            'html_url' => 'https://github.com/user',
            'type' => 'User',
        ],
        'open_issues' => 5,
        'closed_issues' => 10,
        'due_on' => '2024-12-31T23:59:59Z',
        'created_at' => '2024-01-01T00:00:00Z',
        'updated_at' => '2024-06-01T00:00:00Z',
        'closed_at' => null,
        'html_url' => 'https://github.com/owner/repo/milestone/1',
    ], $overrides);
}

describe('ManagesMilestones', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->service = new IssuesService($this->connector);
    });

    it('lists milestones', function () {
        $this->mockClient->addResponse(MockResponse::make([
            fullMilestoneResponse(['number' => 1, 'title' => 'v1.0.0']),
            fullMilestoneResponse(['number' => 2, 'title' => 'v2.0.0', 'state' => 'closed']),
        ]));

        $milestones = $this->service->listMilestones('owner', 'repo');

        expect($milestones)->toHaveCount(2)
            ->and($milestones->first())->toBeInstanceOf(Milestone::class)
            ->and($milestones->first()->title)->toBe('v1.0.0')
            ->and($milestones->last()->title)->toBe('v2.0.0');
    });

    it('lists milestones with filters', function () {
        $this->mockClient->addResponse(MockResponse::make([
            fullMilestoneResponse(['number' => 1, 'title' => 'v1.0.0', 'state' => 'open']),
        ]));

        $milestones = $this->service->listMilestones('owner', 'repo', [
            'state' => 'open',
            'sort' => 'due_on',
            'direction' => 'asc',
        ]);

        expect($milestones)->toHaveCount(1)
            ->and($milestones->first()->state)->toBe('open');
    });

    it('gets single milestone', function () {
        $this->mockClient->addResponse(MockResponse::make(fullMilestoneResponse()));

        $milestone = $this->service->getMilestone('owner', 'repo', 1);

        expect($milestone)->toBeInstanceOf(Milestone::class)
            ->and($milestone->number)->toBe(1)
            ->and($milestone->title)->toBe('v1.0.0');
    });

    it('creates milestone', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullMilestoneResponse([
                'number' => 1,
                'title' => 'v1.0.0',
                'description' => 'First release',
            ]),
            201
        ));

        $milestone = $this->service->createMilestone('owner', 'repo', [
            'title' => 'v1.0.0',
            'description' => 'First release',
        ]);

        expect($milestone)->toBeInstanceOf(Milestone::class)
            ->and($milestone->title)->toBe('v1.0.0')
            ->and($milestone->description)->toBe('First release');
    });

    it('creates milestone with due date', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullMilestoneResponse([
                'title' => 'v1.0.0',
                'due_on' => '2024-12-31T23:59:59Z',
            ]),
            201
        ));

        $milestone = $this->service->createMilestone('owner', 'repo', [
            'title' => 'v1.0.0',
            'due_on' => '2024-12-31T23:59:59Z',
        ]);

        expect($milestone->dueOn)->toBeInstanceOf(DateTime::class)
            ->and($milestone->dueOn->format('Y-m-d'))->toBe('2024-12-31');
    });

    it('updates milestone', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullMilestoneResponse(['title' => 'Updated Title'])
        ));

        $milestone = $this->service->updateMilestone('owner', 'repo', 1, [
            'title' => 'Updated Title',
        ]);

        expect($milestone->title)->toBe('Updated Title');
    });

    it('updates milestone description', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullMilestoneResponse(['description' => 'Updated description'])
        ));

        $milestone = $this->service->updateMilestone('owner', 'repo', 1, [
            'description' => 'Updated description',
        ]);

        expect($milestone->description)->toBe('Updated description');
    });

    it('deletes milestone', function () {
        $this->mockClient->addResponse(MockResponse::make('', 204));

        $result = $this->service->deleteMilestone('owner', 'repo', 1);

        expect($result)->toBeTrue();
    });

    it('closes milestone', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullMilestoneResponse(['state' => 'closed'])
        ));

        $milestone = $this->service->closeMilestone('owner', 'repo', 1);

        expect($milestone->state)->toBe('closed');
    });

    it('reopens milestone', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullMilestoneResponse(['state' => 'open'])
        ));

        $milestone = $this->service->reopenMilestone('owner', 'repo', 1);

        expect($milestone->state)->toBe('open');
    });

    it('validates owner', function () {
        expect(fn () => $this->service->listMilestones('', 'repo'))
            ->toThrow(InvalidArgumentException::class, 'Owner cannot be empty');
    });

    it('validates repository', function () {
        expect(fn () => $this->service->listMilestones('owner', ''))
            ->toThrow(InvalidArgumentException::class, 'Repository name cannot be empty');
    });

    it('validates milestone number', function () {
        expect(fn () => $this->service->getMilestone('owner', 'repo', 0))
            ->toThrow(InvalidArgumentException::class, 'Milestone number must be positive');

        expect(fn () => $this->service->getMilestone('owner', 'repo', -1))
            ->toThrow(InvalidArgumentException::class, 'Milestone number must be positive');
    });

    it('validates milestone title', function () {
        expect(fn () => $this->service->createMilestone('owner', 'repo', [
            'title' => '',
        ]))->toThrow(InvalidArgumentException::class);
    });

    it('validates milestone title length', function () {
        $longTitle = str_repeat('a', 257);

        expect(fn () => $this->service->createMilestone('owner', 'repo', [
            'title' => $longTitle,
        ]))->toThrow(InvalidArgumentException::class, 'title cannot exceed 256 characters');
    });

    it('validates milestone state', function () {
        expect(fn () => $this->service->updateMilestone('owner', 'repo', 1, [
            'state' => 'invalid',
        ]))->toThrow(InvalidArgumentException::class, 'State must be one of: open, closed');
    });

    it('validates due date format', function () {
        expect(fn () => $this->service->createMilestone('owner', 'repo', [
            'title' => 'v1.0.0',
            'due_on' => 'invalid-date',
        ]))->toThrow(InvalidArgumentException::class, 'Due date must be a valid ISO 8601 date string');
    });

    it('allows null description', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullMilestoneResponse(['description' => null]),
            201
        ));

        $milestone = $this->service->createMilestone('owner', 'repo', [
            'title' => 'v1.0.0',
            'description' => null,
        ]);

        expect($milestone->description)->toBeNull();
    });

    it('allows null due date', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullMilestoneResponse(['due_on' => null]),
            201
        ));

        $milestone = $this->service->createMilestone('owner', 'repo', [
            'title' => 'v1.0.0',
            'due_on' => null,
        ]);

        expect($milestone->dueOn)->toBeNull();
    });

    it('handles API errors', function () {
        $this->mockClient->addResponse(MockResponse::make(['message' => 'Not Found'], 404));

        expect(fn () => $this->service->getMilestone('owner', 'repo', 999))
            ->toThrow(Exception::class);
    });

    it('handles validation errors', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'message' => 'Validation Failed',
            'errors' => [
                ['field' => 'title', 'code' => 'missing_field'],
            ],
        ], 422));

        expect(fn () => $this->service->createMilestone('owner', 'repo', []))
            ->toThrow(Exception::class);
    });

    it('returns empty collection when no milestones', function () {
        $this->mockClient->addResponse(MockResponse::make([]));

        $milestones = $this->service->listMilestones('owner', 'repo');

        expect($milestones)->toBeEmpty();
    });

    it('sanitizes milestone data', function () {
        $this->mockClient->addResponse(MockResponse::make(
            fullMilestoneResponse([
                'title' => 'Test',
                'description' => 'Description',
            ]),
            201
        ));

        $milestone = $this->service->createMilestone('owner', 'repo', [
            'title' => '  Test  ',
            'description' => '  Description  ',
        ]);

        expect($milestone)->toBeInstanceOf(Milestone::class);
    });
});
