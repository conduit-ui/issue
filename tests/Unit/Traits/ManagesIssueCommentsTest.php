<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Services\IssuesService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function fullCommentResponse(array $overrides = []): array
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
    ], $overrides);
}

describe('ManagesIssueComments', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->service = new IssuesService($this->connector);
    });

    describe('listComments', function () {
        it('lists comments for an issue', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullCommentResponse(['id' => 1, 'body' => 'First comment']),
                fullCommentResponse(['id' => 2, 'body' => 'Second comment', 'author_association' => 'CONTRIBUTOR']),
            ]));

            $comments = $this->service->listComments('owner', 'repo', 123);

            expect($comments)->toHaveCount(2)
                ->and($comments->first())->toBeInstanceOf(Comment::class)
                ->and($comments->first()->body)->toBe('First comment')
                ->and($comments->first()->authorAssociation)->toBe('OWNER')
                ->and($comments->last()->body)->toBe('Second comment')
                ->and($comments->last()->authorAssociation)->toBe('CONTRIBUTOR');
        });

        it('lists comments with filters', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullCommentResponse(['id' => 1, 'body' => 'Filtered comment']),
            ]));

            $comments = $this->service->listComments('owner', 'repo', 123, ['per_page' => 10, 'page' => 1]);

            expect($comments)->toHaveCount(1)
                ->and($comments->first()->body)->toBe('Filtered comment');
        });

        it('returns empty collection when no comments', function () {
            $this->mockClient->addResponse(MockResponse::make([]));

            $comments = $this->service->listComments('owner', 'repo', 123);

            expect($comments)->toBeEmpty();
        });
    });

    describe('getComment', function () {
        it('gets a single comment by id', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponse(['id' => 456, 'body' => 'Specific comment'])
            ));

            $comment = $this->service->getComment('owner', 'repo', 456);

            expect($comment)->toBeInstanceOf(Comment::class)
                ->and($comment->id)->toBe(456)
                ->and($comment->body)->toBe('Specific comment')
                ->and($comment->user->login)->toBe('testuser');
        });

        it('includes user information', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponse()
            ));

            $comment = $this->service->getComment('owner', 'repo', 1);

            expect($comment->user)->toHaveProperties(['id', 'login', 'avatarUrl', 'htmlUrl', 'type'])
                ->and($comment->user->login)->toBe('testuser');
        });
    });

    describe('createComment', function () {
        it('creates a new comment on an issue', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponse(['body' => 'New comment created']),
                201
            ));

            $comment = $this->service->createComment('owner', 'repo', 123, 'New comment created');

            expect($comment)->toBeInstanceOf(Comment::class)
                ->and($comment->body)->toBe('New comment created');
        });

        it('validates empty comment body', function () {
            expect(fn () => $this->service->createComment('owner', 'repo', 123, ''))
                ->toThrow(InvalidArgumentException::class);
        });

        it('validates repository', function () {
            expect(fn () => $this->service->createComment('', 'repo', 123, 'Comment'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('validates issue number', function () {
            expect(fn () => $this->service->createComment('owner', 'repo', 0, 'Comment'))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('updateComment', function () {
        it('updates an existing comment', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponse(['body' => 'Updated comment text'])
            ));

            $comment = $this->service->updateComment('owner', 'repo', 456, 'Updated comment text');

            expect($comment)->toBeInstanceOf(Comment::class)
                ->and($comment->body)->toBe('Updated comment text');
        });

        it('validates empty comment body', function () {
            expect(fn () => $this->service->updateComment('owner', 'repo', 456, ''))
                ->toThrow(InvalidArgumentException::class);
        });

        it('validates comment id', function () {
            expect(fn () => $this->service->updateComment('owner', 'repo', 0, 'Updated'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('validates repository', function () {
            expect(fn () => $this->service->updateComment('', 'repo', 456, 'Updated'))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('deleteComment', function () {
        it('deletes a comment successfully', function () {
            $this->mockClient->addResponse(MockResponse::make('', 204));

            $result = $this->service->deleteComment('owner', 'repo', 456);

            expect($result)->toBeTrue();
        });

        it('validates comment id', function () {
            expect(fn () => $this->service->deleteComment('owner', 'repo', 0))
                ->toThrow(InvalidArgumentException::class);
        });

        it('validates repository', function () {
            expect(fn () => $this->service->deleteComment('', 'repo', 456))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('author association methods', function () {
        it('identifies owner comments', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponse(['author_association' => 'OWNER'])
            ));

            $comment = $this->service->getComment('owner', 'repo', 1);

            expect($comment->isOwner())->toBeTrue()
                ->and($comment->isMember())->toBeFalse()
                ->and($comment->isContributor())->toBeFalse();
        });

        it('identifies member comments', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponse(['author_association' => 'MEMBER'])
            ));

            $comment = $this->service->getComment('owner', 'repo', 1);

            expect($comment->isOwner())->toBeFalse()
                ->and($comment->isMember())->toBeTrue()
                ->and($comment->isContributor())->toBeFalse();
        });

        it('identifies contributor comments', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponse(['author_association' => 'CONTRIBUTOR'])
            ));

            $comment = $this->service->getComment('owner', 'repo', 1);

            expect($comment->isOwner())->toBeFalse()
                ->and($comment->isMember())->toBeFalse()
                ->and($comment->isContributor())->toBeTrue();
        });
    });
});
