<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Reaction;
use ConduitUI\Issue\Services\IssuesService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function fullReactionResponse(array $overrides = []): array
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

describe('ManagesCommentReactions', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->service = new IssuesService($this->connector);
    });

    describe('listCommentReactions', function () {
        it('lists reactions for a comment', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullReactionResponse(['id' => 1, 'content' => '+1']),
                fullReactionResponse(['id' => 2, 'content' => 'heart']),
            ]));

            $reactions = $this->service->listCommentReactions('owner', 'repo', 123);

            expect($reactions)->toHaveCount(2)
                ->and($reactions->first())->toBeInstanceOf(Reaction::class)
                ->and($reactions->first()->content)->toBe('+1')
                ->and($reactions->last()->content)->toBe('heart');
        });

        it('lists reactions with filters', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullReactionResponse(['id' => 1, 'content' => '+1']),
            ]));

            $reactions = $this->service->listCommentReactions('owner', 'repo', 123, ['content' => '+1']);

            expect($reactions)->toHaveCount(1)
                ->and($reactions->first()->content)->toBe('+1');
        });

        it('returns empty collection when no reactions', function () {
            $this->mockClient->addResponse(MockResponse::make([]));

            $reactions = $this->service->listCommentReactions('owner', 'repo', 123);

            expect($reactions)->toBeEmpty();
        });

        it('validates repository', function () {
            expect(fn () => $this->service->listCommentReactions('', 'repo', 123))
                ->toThrow(InvalidArgumentException::class);
        });

        it('validates comment id', function () {
            expect(fn () => $this->service->listCommentReactions('owner', 'repo', 0))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('createCommentReaction', function () {
        it('creates a new reaction on a comment', function () {
            $this->mockClient->addResponse(MockResponse::make(
                fullReactionResponse(['content' => 'heart']),
                201
            ));

            $reaction = $this->service->createCommentReaction('owner', 'repo', 123, 'heart');

            expect($reaction)->toBeInstanceOf(Reaction::class)
                ->and($reaction->content)->toBe('heart');
        });

        it('validates invalid reaction content', function () {
            expect(fn () => $this->service->createCommentReaction('owner', 'repo', 123, 'invalid'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('validates empty reaction content', function () {
            expect(fn () => $this->service->createCommentReaction('owner', 'repo', 123, ''))
                ->toThrow(InvalidArgumentException::class);
        });

        it('validates repository', function () {
            expect(fn () => $this->service->createCommentReaction('', 'repo', 123, '+1'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('validates comment id', function () {
            expect(fn () => $this->service->createCommentReaction('owner', 'repo', 0, '+1'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('creates all valid reaction types', function () {
            $types = ['+1', '-1', 'laugh', 'confused', 'heart', 'hooray', 'rocket', 'eyes'];

            foreach ($types as $type) {
                $this->mockClient->addResponse(MockResponse::make(
                    fullReactionResponse(['content' => $type]),
                    201
                ));

                $reaction = $this->service->createCommentReaction('owner', 'repo', 123, $type);

                expect($reaction->content)->toBe($type);
            }
        });
    });

    describe('deleteCommentReaction', function () {
        it('deletes a reaction successfully', function () {
            $this->mockClient->addResponse(MockResponse::make('', 204));

            $result = $this->service->deleteCommentReaction('owner', 'repo', 123, 456);

            expect($result)->toBeTrue();
        });

        it('validates comment id', function () {
            expect(fn () => $this->service->deleteCommentReaction('owner', 'repo', 0, 456))
                ->toThrow(InvalidArgumentException::class);
        });

        it('validates reaction id', function () {
            expect(fn () => $this->service->deleteCommentReaction('owner', 'repo', 123, 0))
                ->toThrow(InvalidArgumentException::class);
        });

        it('validates repository', function () {
            expect(fn () => $this->service->deleteCommentReaction('', 'repo', 123, 456))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('reaction type identification', function () {
        it('identifies thumbs up reactions', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullReactionResponse(['content' => '+1']),
            ]));

            $reactions = $this->service->listCommentReactions('owner', 'repo', 123);

            expect($reactions->first()->isThumbsUp())->toBeTrue()
                ->and($reactions->first()->isThumbsDown())->toBeFalse();
        });

        it('identifies thumbs down reactions', function () {
            $this->mockClient->addResponse(MockResponse::make([
                fullReactionResponse(['content' => '-1']),
            ]));

            $reactions = $this->service->listCommentReactions('owner', 'repo', 123);

            expect($reactions->first()->isThumbsDown())->toBeTrue()
                ->and($reactions->first()->isThumbsUp())->toBeFalse();
        });
    });
});
