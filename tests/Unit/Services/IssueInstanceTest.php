<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Services\IssueInstance;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->mockClient = new MockClient;
    $this->connector = new Connector('fake-token');
    $this->connector->withMockClient($this->mockClient);

    $this->defaultIssueData = [
        'id' => 123,
        'number' => 1,
        'title' => 'Test Issue',
        'body' => 'Test body',
        'state' => 'open',
        'locked' => false,
        'assignees' => [],
        'labels' => [],
        'milestone' => null,
        'comments' => 0,
        'created_at' => '2023-01-01T12:00:00Z',
        'updated_at' => '2023-01-02T12:00:00Z',
        'closed_at' => null,
        'html_url' => 'https://github.com/owner/repo/issues/1',
        'url' => 'https://api.github.com/repos/owner/repo/issues/1',
        'active_lock_reason' => null,
        'user' => [
            'id' => 101,
            'login' => 'author',
            'avatar_url' => 'https://github.com/author.png',
            'html_url' => 'https://github.com/author',
            'type' => 'User',
        ],
    ];
});

describe('IssueInstance', function () {
    it('can get issue data', function () {
        $this->mockClient->addResponses([MockResponse::make($this->defaultIssueData)]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $issue = $instance->get();

        expect($issue)->toBeInstanceOf(Issue::class);
        expect($issue->number)->toBe(1);
        expect($issue->title)->toBe('Test Issue');
    });

    it('caches issue data on subsequent get calls', function () {
        $this->mockClient->addResponses([MockResponse::make($this->defaultIssueData)]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);

        $issue1 = $instance->get();
        $issue2 = $instance->get();

        expect($issue1)->toBe($issue2);
        expect($this->mockClient->getRecordedResponses())->toHaveCount(1);
    });

    it('can refresh issue data', function () {
        $this->mockClient->addResponses([
            MockResponse::make($this->defaultIssueData),
            MockResponse::make(array_merge($this->defaultIssueData, ['title' => 'Updated Title'])),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);

        $issue1 = $instance->get();
        expect($issue1->title)->toBe('Test Issue');

        $issue2 = $instance->fresh();
        expect($issue2->title)->toBe('Updated Title');
    });

    it('can update issue title', function () {
        $this->mockClient->addResponses([
            MockResponse::make(array_merge($this->defaultIssueData, ['title' => 'New Title'])),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->title('New Title');

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->title)->toBe('New Title');
    });

    it('can update issue body', function () {
        $this->mockClient->addResponses([
            MockResponse::make(array_merge($this->defaultIssueData, ['body' => 'New body content'])),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->body('New body content');

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->body)->toBe('New body content');
    });

    it('can close issue', function () {
        $this->mockClient->addResponses([
            MockResponse::make(array_merge($this->defaultIssueData, [
                'state' => 'closed',
                'closed_at' => '2023-01-03T12:00:00Z',
            ])),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->close();

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->state)->toBe('closed');
        expect($instance->get()->isClosed())->toBeTrue();
    });

    it('can close issue with reason', function () {
        $this->mockClient->addResponses([
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'closed',
                'state_reason' => 'completed',
                'locked' => false,
                'assignees' => [],
                'labels' => [],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => '2023-01-03T12:00:00Z',
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->close('completed');

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->state)->toBe('closed');
    });

    it('can reopen issue', function () {
        $this->mockClient->addResponses([
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'assignees' => [],
                'labels' => [],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->reopen();

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->state)->toBe('open');
        expect($instance->get()->isOpen())->toBeTrue();
    });

    it('can add single label', function () {
        $this->mockClient->addResponses([
            MockResponse::make([
                ['id' => 1, 'name' => 'bug', 'color' => 'fc2929', 'description' => null],
            ], 200),
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'assignees' => [],
                'labels' => [
                    ['id' => 1, 'name' => 'bug', 'color' => 'fc2929', 'description' => null],
                ],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->addLabel('bug');

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->labels)->toHaveCount(1);
    });

    it('can add multiple labels', function () {
        $this->mockClient->addResponses([
            MockResponse::make([
                ['id' => 1, 'name' => 'bug', 'color' => 'fc2929', 'description' => null],
                ['id' => 2, 'name' => 'urgent', 'color' => 'ff0000', 'description' => null],
            ], 200),
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'assignees' => [],
                'labels' => [
                    ['id' => 1, 'name' => 'bug', 'color' => 'fc2929', 'description' => null],
                    ['id' => 2, 'name' => 'urgent', 'color' => 'ff0000', 'description' => null],
                ],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->addLabels(['bug', 'urgent']);

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->labels)->toHaveCount(2);
    });

    it('can remove label', function () {
        $this->mockClient->addResponses([
            MockResponse::make([], 204),
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'assignees' => [],
                'labels' => [],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->removeLabel('bug');

        expect($result)->toBeInstanceOf(IssueInstance::class);
    });

    it('can set labels replacing all existing', function () {
        $this->mockClient->addResponses([
            MockResponse::make([
                ['id' => 3, 'name' => 'enhancement', 'color' => '84b6eb', 'description' => null],
            ], 200),
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'assignees' => [],
                'labels' => [
                    ['id' => 3, 'name' => 'enhancement', 'color' => '84b6eb', 'description' => null],
                ],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->setLabels(['enhancement']);

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->labels)->toHaveCount(1);
    });

    it('can assign user', function () {
        $this->mockClient->addResponses([
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'assignees' => [
                    [
                        'id' => 201,
                        'login' => 'developer',
                        'avatar_url' => 'https://github.com/developer.png',
                        'html_url' => 'https://github.com/developer',
                        'type' => 'User',
                    ],
                ],
                'labels' => [],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->assignTo('developer');

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->assignees)->toHaveCount(1);
    });

    it('can assign multiple users', function () {
        $this->mockClient->addResponses([
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'assignees' => [
                    [
                        'id' => 201,
                        'login' => 'developer1',
                        'avatar_url' => 'https://github.com/developer1.png',
                        'html_url' => 'https://github.com/developer1',
                        'type' => 'User',
                    ],
                    [
                        'id' => 202,
                        'login' => 'developer2',
                        'avatar_url' => 'https://github.com/developer2.png',
                        'html_url' => 'https://github.com/developer2',
                        'type' => 'User',
                    ],
                ],
                'labels' => [],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->assign(['developer1', 'developer2']);

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->assignees)->toHaveCount(2);
    });

    it('can unassign users', function () {
        $this->mockClient->addResponses([
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'assignees' => [
                    [
                        'id' => 201,
                        'login' => 'developer1',
                        'avatar_url' => 'https://github.com/developer1.png',
                        'html_url' => 'https://github.com/developer1',
                        'type' => 'User',
                    ],
                    [
                        'id' => 202,
                        'login' => 'developer2',
                        'avatar_url' => 'https://github.com/developer2.png',
                        'html_url' => 'https://github.com/developer2',
                        'type' => 'User',
                    ],
                ],
                'labels' => [],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'assignees' => [
                    [
                        'id' => 202,
                        'login' => 'developer2',
                        'avatar_url' => 'https://github.com/developer2.png',
                        'html_url' => 'https://github.com/developer2',
                        'type' => 'User',
                    ],
                ],
                'labels' => [],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $instance->get(); // Load initial state
        $result = $instance->unassign('developer1');

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->assignees)->toHaveCount(1);
    });

    it('can set milestone', function () {
        $this->mockClient->addResponses([
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'assignees' => [],
                'labels' => [],
                'milestone' => [
                    'title' => 'v1.0',
                ],
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->milestone(1);

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->milestone)->toBe('v1.0');
    });

    it('can lock issue', function () {
        $this->mockClient->addResponses([
            MockResponse::make([], 204),
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => true,
                'active_lock_reason' => 'too heated',
                'assignees' => [],
                'labels' => [],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->lock('too heated');

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->locked)->toBeTrue();
    });

    it('can unlock issue', function () {
        $this->mockClient->addResponses([
            MockResponse::make([], 204),
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'active_lock_reason' => null,
                'assignees' => [],
                'labels' => [],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $result = $instance->unlock();

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->locked)->toBeFalse();
    });

    it('can add comment', function () {
        $this->mockClient->addResponses([
            MockResponse::make([
                'id' => 999,
                'body' => 'Test comment',
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-01T12:00:00Z',
                'html_url' => 'https://github.com/owner/repo/issues/1#issuecomment-999',
                'url' => 'https://api.github.com/repos/owner/repo/issues/comments/999',
                'author_association' => 'OWNER',
            ], 201),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);
        $comment = $instance->comment('Test comment');

        expect($comment)->toBeInstanceOf(Comment::class);
        expect($comment->body)->toBe('Test comment');
    });

    it('supports method chaining', function () {
        $this->mockClient->addResponses([
            // addLabels() POST response
            MockResponse::make([
                ['id' => 1, 'name' => 'bug', 'color' => 'fc2929', 'description' => null],
            ], 200),
            // addLabels() fresh() GET response
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'assignees' => [],
                'labels' => [
                    ['id' => 1, 'name' => 'bug', 'color' => 'fc2929', 'description' => null],
                ],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
            // assign()
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'open',
                'locked' => false,
                'assignees' => [
                    [
                        'id' => 201,
                        'login' => 'developer',
                        'avatar_url' => 'https://github.com/developer.png',
                        'html_url' => 'https://github.com/developer',
                        'type' => 'User',
                    ],
                ],
                'labels' => [
                    ['id' => 1, 'name' => 'bug', 'color' => 'fc2929', 'description' => null],
                ],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => null,
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
            // close()
            MockResponse::make([
                'id' => 123,
                'number' => 1,
                'title' => 'Test Issue',
                'body' => 'Test body',
                'state' => 'closed',
                'locked' => false,
                'assignees' => [
                    [
                        'id' => 201,
                        'login' => 'developer',
                        'avatar_url' => 'https://github.com/developer.png',
                        'html_url' => 'https://github.com/developer',
                        'type' => 'User',
                    ],
                ],
                'labels' => [
                    ['id' => 1, 'name' => 'bug', 'color' => 'fc2929', 'description' => null],
                ],
                'milestone' => null,
                'comments' => 0,
                'created_at' => '2023-01-01T12:00:00Z',
                'updated_at' => '2023-01-02T12:00:00Z',
                'closed_at' => '2023-01-03T12:00:00Z',
                'html_url' => 'https://github.com/owner/repo/issues/1',
                'url' => 'https://api.github.com/repos/owner/repo/issues/1',
                'active_lock_reason' => null,
                'user' => [
                    'id' => 101,
                    'login' => 'author',
                    'avatar_url' => 'https://github.com/author.png',
                    'html_url' => 'https://github.com/author',
                    'type' => 'User',
                ],
            ], 200),
        ]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);

        $result = $instance
            ->addLabels(['bug'])
            ->assign('developer')
            ->close();

        expect($result)->toBeInstanceOf(IssueInstance::class);
        expect($instance->get()->state)->toBe('closed');
        expect($instance->get()->labels)->toHaveCount(1);
        expect($instance->get()->assignees)->toHaveCount(1);
    });

    it('can access issue properties via magic getter', function () {
        $this->mockClient->addResponses([MockResponse::make($this->defaultIssueData)]);

        $instance = new IssueInstance($this->connector, 'owner/repo', 1);

        expect($instance->title)->toBe('Test Issue');
        expect($instance->number)->toBe(1);
        expect($instance->state)->toBe('open');
    });
});
