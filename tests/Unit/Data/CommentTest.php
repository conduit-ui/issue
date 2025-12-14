<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Comment;
use ConduitUI\Issue\Data\User;

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
});
