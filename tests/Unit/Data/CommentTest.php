<?php

declare(strict_types=1);

use ConduitUI\Issue\Contracts\ManagesCommentReactionsInterface;
use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Data\Reaction;
use ConduitUI\Issue\Data\User;
use Illuminate\Support\Collection;

describe('Comment', function () {
    it('creates from array', function () {
        $data = [
            'id' => 123,
            'body' => 'Test comment body',
            'user' => [
                'id' => 1,
                'login' => 'testuser',
                'avatar_url' => 'https://example.com/avatar.png',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-02T00:00:00Z',
            'html_url' => 'https://github.com/owner/repo/issues/1#issuecomment-123',
            'author_association' => 'OWNER',
        ];

        $comment = Comment::fromArray($data);

        expect($comment->id)->toBe(123)
            ->and($comment->body)->toBe('Test comment body')
            ->and($comment->user)->toBeInstanceOf(User::class)
            ->and($comment->user->login)->toBe('testuser')
            ->and($comment->createdAt->format('Y-m-d'))->toBe('2024-01-01')
            ->and($comment->updatedAt->format('Y-m-d'))->toBe('2024-01-02')
            ->and($comment->htmlUrl)->toBe('https://github.com/owner/repo/issues/1#issuecomment-123')
            ->and($comment->authorAssociation)->toBe('OWNER');
    });

    it('converts to array', function () {
        $data = [
            'id' => 123,
            'body' => 'Test comment body',
            'user' => [
                'id' => 1,
                'login' => 'testuser',
                'avatar_url' => 'https://example.com/avatar.png',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-02T00:00:00Z',
            'html_url' => 'https://github.com/owner/repo/issues/1#issuecomment-123',
            'author_association' => 'OWNER',
        ];

        $comment = Comment::fromArray($data);
        $array = $comment->toArray();

        expect($array)->toHaveKey('id', 123)
            ->and($array)->toHaveKey('body', 'Test comment body')
            ->and($array)->toHaveKey('user')
            ->and($array['user'])->toHaveKey('login', 'testuser')
            ->and($array)->toHaveKey('created_at')
            ->and($array)->toHaveKey('updated_at')
            ->and($array)->toHaveKey('html_url')
            ->and($array)->toHaveKey('author_association', 'OWNER');
    });

    describe('author association methods', function () {
        it('identifies owner', function () {
            $comment = Comment::fromArray([
                'id' => 1,
                'body' => 'Test',
                'user' => [
                    'id' => 1,
                    'login' => 'owner',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/owner',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'html_url' => 'https://github.com/test/test/issues/1#issuecomment-1',
                'author_association' => 'OWNER',
            ]);

            expect($comment->isOwner())->toBeTrue()
                ->and($comment->isMember())->toBeFalse()
                ->and($comment->isContributor())->toBeFalse();
        });

        it('identifies member', function () {
            $comment = Comment::fromArray([
                'id' => 1,
                'body' => 'Test',
                'user' => [
                    'id' => 1,
                    'login' => 'member',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/member',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'html_url' => 'https://github.com/test/test/issues/1#issuecomment-1',
                'author_association' => 'MEMBER',
            ]);

            expect($comment->isOwner())->toBeFalse()
                ->and($comment->isMember())->toBeTrue()
                ->and($comment->isContributor())->toBeFalse();
        });

        it('identifies contributor', function () {
            $comment = Comment::fromArray([
                'id' => 1,
                'body' => 'Test',
                'user' => [
                    'id' => 1,
                    'login' => 'contributor',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/contributor',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'html_url' => 'https://github.com/test/test/issues/1#issuecomment-1',
                'author_association' => 'CONTRIBUTOR',
            ]);

            expect($comment->isOwner())->toBeFalse()
                ->and($comment->isMember())->toBeFalse()
                ->and($comment->isContributor())->toBeTrue();
        });

        it('handles other author associations', function () {
            $comment = Comment::fromArray([
                'id' => 1,
                'body' => 'Test',
                'user' => [
                    'id' => 1,
                    'login' => 'none',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/none',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'html_url' => 'https://github.com/test/test/issues/1#issuecomment-1',
                'author_association' => 'NONE',
            ]);

            expect($comment->isOwner())->toBeFalse()
                ->and($comment->isMember())->toBeFalse()
                ->and($comment->isContributor())->toBeFalse();
        });
    });

    describe('fluent reaction methods', function () {
        function createCommentWithContext(): array
        {
            $mockService = Mockery::mock(ManagesCommentReactionsInterface::class);
            $comment = Comment::fromArray([
                'id' => 123,
                'body' => 'Test comment',
                'user' => [
                    'id' => 1,
                    'login' => 'testuser',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/testuser',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'html_url' => 'https://github.com/owner/repo/issues/1#issuecomment-123',
                'author_association' => 'OWNER',
            ])->withContext('owner', 'repo', $mockService);

            return [$comment, $mockService];
        }

        it('can react to a comment', function () {
            [$comment, $mockService] = createCommentWithContext();

            $expectedReaction = Reaction::fromArray([
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
            ]);

            $mockService->shouldReceive('createCommentReaction')
                ->with('owner', 'repo', 123, '+1')
                ->once()
                ->andReturn($expectedReaction);

            $reaction = $comment->react('+1');

            expect($reaction)->toBeInstanceOf(Reaction::class)
                ->and($reaction->content)->toBe('+1');
        });

        it('can list reactions on a comment', function () {
            [$comment, $mockService] = createCommentWithContext();

            $expectedReactions = collect([
                Reaction::fromArray([
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
                ]),
            ]);

            $mockService->shouldReceive('listCommentReactions')
                ->with('owner', 'repo', 123, [])
                ->once()
                ->andReturn($expectedReactions);

            $reactions = $comment->reactions();

            expect($reactions)->toBeInstanceOf(Collection::class)
                ->and($reactions)->toHaveCount(1);
        });

        it('can unreact from a comment', function () {
            [$comment, $mockService] = createCommentWithContext();

            $mockService->shouldReceive('deleteCommentReaction')
                ->with('owner', 'repo', 123, 456)
                ->once()
                ->andReturn(true);

            $result = $comment->unreact(456);

            expect($result)->toBeTrue();
        });

        it('throws exception when context not set', function () {
            $comment = Comment::fromArray([
                'id' => 123,
                'body' => 'Test comment',
                'user' => [
                    'id' => 1,
                    'login' => 'testuser',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/testuser',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'html_url' => 'https://github.com/owner/repo/issues/1#issuecomment-123',
                'author_association' => 'OWNER',
            ]);

            expect(fn () => $comment->react('+1'))
                ->toThrow(RuntimeException::class, 'Comment context not set');
        });
    });
});
