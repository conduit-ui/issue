<?php

declare(strict_types=1);

use ConduitUI\Issue\Data\Reaction;
use ConduitUI\Issue\Data\User;

describe('Reaction', function () {
    it('creates from array', function () {
        $data = [
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
        ];

        $reaction = Reaction::fromArray($data);

        expect($reaction->id)->toBe(1)
            ->and($reaction->content)->toBe('+1')
            ->and($reaction->user)->toBeInstanceOf(User::class)
            ->and($reaction->user->login)->toBe('testuser')
            ->and($reaction->createdAt->format('Y-m-d'))->toBe('2024-01-01');
    });

    it('converts to array', function () {
        $data = [
            'id' => 123,
            'content' => 'heart',
            'user' => [
                'id' => 1,
                'login' => 'testuser',
                'avatar_url' => 'https://example.com/avatar.png',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'created_at' => '2024-01-01T00:00:00Z',
        ];

        $reaction = Reaction::fromArray($data);
        $array = $reaction->toArray();

        expect($array)->toHaveKey('id', 123)
            ->and($array)->toHaveKey('content', 'heart')
            ->and($array)->toHaveKey('user')
            ->and($array['user'])->toHaveKey('login', 'testuser')
            ->and($array)->toHaveKey('created_at');
    });

    describe('reaction type methods', function () {
        it('identifies thumbs up reaction', function () {
            $reaction = Reaction::fromArray([
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

            expect($reaction->isThumbsUp())->toBeTrue()
                ->and($reaction->isThumbsDown())->toBeFalse()
                ->and($reaction->isLaugh())->toBeFalse()
                ->and($reaction->isConfused())->toBeFalse()
                ->and($reaction->isHeart())->toBeFalse()
                ->and($reaction->isHooray())->toBeFalse()
                ->and($reaction->isRocket())->toBeFalse()
                ->and($reaction->isEyes())->toBeFalse();
        });

        it('identifies thumbs down reaction', function () {
            $reaction = Reaction::fromArray([
                'id' => 1,
                'content' => '-1',
                'user' => [
                    'id' => 1,
                    'login' => 'testuser',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/testuser',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
            ]);

            expect($reaction->isThumbsUp())->toBeFalse()
                ->and($reaction->isThumbsDown())->toBeTrue();
        });

        it('identifies laugh reaction', function () {
            $reaction = Reaction::fromArray([
                'id' => 1,
                'content' => 'laugh',
                'user' => [
                    'id' => 1,
                    'login' => 'testuser',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/testuser',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
            ]);

            expect($reaction->isLaugh())->toBeTrue();
        });

        it('identifies confused reaction', function () {
            $reaction = Reaction::fromArray([
                'id' => 1,
                'content' => 'confused',
                'user' => [
                    'id' => 1,
                    'login' => 'testuser',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/testuser',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
            ]);

            expect($reaction->isConfused())->toBeTrue();
        });

        it('identifies heart reaction', function () {
            $reaction = Reaction::fromArray([
                'id' => 1,
                'content' => 'heart',
                'user' => [
                    'id' => 1,
                    'login' => 'testuser',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/testuser',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
            ]);

            expect($reaction->isHeart())->toBeTrue();
        });

        it('identifies hooray reaction', function () {
            $reaction = Reaction::fromArray([
                'id' => 1,
                'content' => 'hooray',
                'user' => [
                    'id' => 1,
                    'login' => 'testuser',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/testuser',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
            ]);

            expect($reaction->isHooray())->toBeTrue();
        });

        it('identifies rocket reaction', function () {
            $reaction = Reaction::fromArray([
                'id' => 1,
                'content' => 'rocket',
                'user' => [
                    'id' => 1,
                    'login' => 'testuser',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/testuser',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
            ]);

            expect($reaction->isRocket())->toBeTrue();
        });

        it('identifies eyes reaction', function () {
            $reaction = Reaction::fromArray([
                'id' => 1,
                'content' => 'eyes',
                'user' => [
                    'id' => 1,
                    'login' => 'testuser',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'html_url' => 'https://github.com/testuser',
                    'type' => 'User',
                ],
                'created_at' => '2024-01-01T00:00:00Z',
            ]);

            expect($reaction->isEyes())->toBeTrue();
        });
    });

    describe('valid reaction types', function () {
        it('returns all valid reaction content types', function () {
            $validTypes = Reaction::validTypes();

            expect($validTypes)->toBe(['+1', '-1', 'laugh', 'confused', 'heart', 'hooray', 'rocket', 'eyes']);
        });

        it('validates a valid reaction type', function () {
            expect(Reaction::isValidType('+1'))->toBeTrue()
                ->and(Reaction::isValidType('-1'))->toBeTrue()
                ->and(Reaction::isValidType('laugh'))->toBeTrue()
                ->and(Reaction::isValidType('confused'))->toBeTrue()
                ->and(Reaction::isValidType('heart'))->toBeTrue()
                ->and(Reaction::isValidType('hooray'))->toBeTrue()
                ->and(Reaction::isValidType('rocket'))->toBeTrue()
                ->and(Reaction::isValidType('eyes'))->toBeTrue();
        });

        it('rejects an invalid reaction type', function () {
            expect(Reaction::isValidType('invalid'))->toBeFalse()
                ->and(Reaction::isValidType(''))->toBeFalse()
                ->and(Reaction::isValidType('thumbsup'))->toBeFalse();
        });
    });
});
