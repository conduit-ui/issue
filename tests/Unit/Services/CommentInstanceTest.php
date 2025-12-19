<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Data\Reaction;
use ConduitUI\Issue\Services\CommentInstance;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function fullCommentResponseForInstance(array $overrides = []): array
{
    return array_merge([
        'id' => 456,
        'body' => 'Test comment body',
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.png',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'created_at' => '2024-01-01T00:00:00Z',
        'updated_at' => '2024-01-01T00:00:00Z',
        'html_url' => 'https://github.com/owner/repo/issues/123#issuecomment-456',
        'author_association' => 'OWNER',
        'issue_url' => 'https://api.github.com/repos/owner/repo/issues/123',
    ], $overrides);
}

function reactionResponseForInstance(array $overrides = []): array
{
    return array_merge([
        'id' => 1,
        'content' => '+1',
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.png',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'created_at' => '2024-01-01T00:00:00Z',
    ], $overrides);
}

describe('CommentInstance', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->instance = new CommentInstance($this->connector, 'owner/repo', 456);
    });

    describe('get', function () {
        it('fetches the comment data', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponseForInstance(['body' => 'Fetched comment'])
            ));

            $comment = $this->instance->get();

            expect($comment)->toBeInstanceOf(Comment::class)
                ->and($comment->id)->toBe(456)
                ->and($comment->body)->toBe('Fetched comment');
        });

        it('caches the comment after first fetch', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponseForInstance(['body' => 'Cached comment'])
            ));

            $comment1 = $this->instance->get();
            $comment2 = $this->instance->get();

            // Verify both calls return the same instance and body
            expect($comment1)->toBe($comment2)
                ->and($comment1->body)->toBe('Cached comment')
                ->and($comment2->body)->toBe('Cached comment');
        });
    });

    describe('update', function () {
        it('updates the comment body', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponseForInstance(['body' => 'Updated body'])
            ));

            $result = $this->instance->update('Updated body');

            expect($result)->toBe($this->instance)
                ->and($result->get()->body)->toBe('Updated body');
        });

        it('clears the cache after update', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponseForInstance(['body' => 'First'])
            ));
            $this->mockClient->addResponse(MockResponse::make(
                fullCommentResponseForInstance(['body' => 'Updated'])
            ));

            $this->instance->get();
            $this->instance->update('Updated');

            expect($this->instance->get()->body)->toBe('Updated');
        });
    });

    describe('delete', function () {
        it('deletes the comment successfully', function () {
            $this->mockClient->addResponse(MockResponse::make('', 204));

            $result = $this->instance->delete();

            expect($result)->toBeTrue();
        });

        it('returns false on failed deletion', function () {
            $this->mockClient->addResponse(MockResponse::make('', 404));

            $result = $this->instance->delete();

            expect($result)->toBeFalse();
        });
    });

    describe('react', function () {
        it('adds a reaction to the comment', function () {
            $this->mockClient->addResponse(MockResponse::make(
                reactionResponseForInstance(['content' => 'heart']),
                201
            ));

            $reaction = $this->instance->react('heart');

            expect($reaction)->toBeInstanceOf(Reaction::class)
                ->and($reaction->content)->toBe('heart');
        });

        it('supports all reaction types', function () {
            $reactionTypes = ['+1', '-1', 'laugh', 'confused', 'heart', 'hooray', 'rocket', 'eyes'];

            foreach ($reactionTypes as $type) {
                $this->mockClient->addResponse(MockResponse::make(
                    reactionResponseForInstance(['content' => $type]),
                    201
                ));

                $reaction = $this->instance->react($type);
                expect($reaction->content)->toBe($type);
            }
        });
    });

    describe('reactions', function () {
        it('gets all reactions on the comment', function () {
            $this->mockClient->addResponse(MockResponse::make([
                reactionResponseForInstance(['id' => 1, 'content' => '+1']),
                reactionResponseForInstance(['id' => 2, 'content' => 'heart']),
            ]));

            $reactions = $this->instance->reactions();

            expect($reactions)->toHaveCount(2)
                ->and($reactions->first())->toBeInstanceOf(Reaction::class)
                ->and($reactions->first()->content)->toBe('+1')
                ->and($reactions->last()->content)->toBe('heart');
        });

        it('returns empty collection when no reactions exist', function () {
            $this->mockClient->addResponse(MockResponse::make([]));

            $reactions = $this->instance->reactions();

            expect($reactions)->toBeEmpty();
        });
    });

    describe('deleteReaction', function () {
        it('deletes a reaction successfully', function () {
            $this->mockClient->addResponse(MockResponse::make('', 204));

            $result = $this->instance->deleteReaction(789);

            expect($result)->toBeTrue();
        });

        it('returns false on failed deletion', function () {
            $this->mockClient->addResponse(MockResponse::make('', 404));

            $result = $this->instance->deleteReaction(789);

            expect($result)->toBeFalse();
        });
    });
});
