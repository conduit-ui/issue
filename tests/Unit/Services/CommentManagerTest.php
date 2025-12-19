<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Services\CommentInstance;
use ConduitUI\Issue\Services\CommentManager;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function fullCommentResponseForManager(array $overrides = []): array
{
    return array_merge([
        'id' => 1,
        'body' => 'This is a test comment',
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.png',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'created_at' => '2024-01-01T00:00:00Z',
        'updated_at' => '2024-01-01T00:00:00Z',
        'html_url' => 'https://github.com/owner/repo/issues/123#issuecomment-1',
        'author_association' => 'OWNER',
        'issue_url' => 'https://api.github.com/repos/owner/repo/issues/123',
    ], $overrides);
}

describe('CommentManager', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->manager = new CommentManager($this->connector, 'owner/repo', 123);
    });

    describe('all', function () {
        it('gets all comments for an issue', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullCommentResponseForManager(['id' => 1, 'body' => 'First comment']),
                fullCommentResponseForManager(['id' => 2, 'body' => 'Second comment']),
            ]));

            $comments = $this->manager->all();

            expect($comments)->toHaveCount(2)
                ->and($comments->first())->toBeInstanceOf(Comment::class)
                ->and($comments->first()->body)->toBe('First comment')
                ->and($comments->last()->body)->toBe('Second comment');
        });

        it('returns empty collection when no comments exist', function () {
            $this->mockClient->addResponse(MockResponse::make([]));

            $comments = $this->manager->all();

            expect($comments)->toBeEmpty();
        });
    });

    describe('find', function () {
        it('gets a specific comment by id', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponseForManager(['id' => 456, 'body' => 'Specific comment'])
            ));

            $comment = $this->manager->find(456);

            expect($comment)->toBeInstanceOf(Comment::class)
                ->and($comment->id)->toBe(456)
                ->and($comment->body)->toBe('Specific comment');
        });
    });

    describe('create', function () {
        it('creates a new comment and returns CommentInstance', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponseForManager(['id' => 789, 'body' => 'New comment']),
                201
            ));

            $instance = $this->manager->create('New comment');

            expect($instance)->toBeInstanceOf(CommentInstance::class);
        });
    });

    describe('update', function () {
        it('updates an existing comment', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponseForManager(['id' => 456, 'body' => 'Updated body'])
            ));

            $comment = $this->manager->update(456, 'Updated body');

            expect($comment)->toBeInstanceOf(Comment::class)
                ->and($comment->body)->toBe('Updated body');
        });
    });

    describe('delete', function () {
        it('deletes a comment successfully', function () {
            $this->mockClient->addResponse(MockResponse::make('', 204));

            $result = $this->manager->delete(456);

            expect($result)->toBeTrue();
        });

        it('returns false on failed deletion', function () {
            $this->mockClient->addResponse(MockResponse::make('', 404));

            $result = $this->manager->delete(456);

            expect($result)->toBeFalse();
        });
    });

    describe('comment', function () {
        it('returns a CommentInstance for chaining', function () {
            $instance = $this->manager->comment(456);

            expect($instance)->toBeInstanceOf(CommentInstance::class);
        });
    });
});
