<?php

declare(strict_types=1);

use ConduitUI\Issue\Security\TokenSanitizer;

describe('TokenSanitizer', function () {
    it('sanitizes github classic tokens', function () {
        $token = 'ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        $result = TokenSanitizer::sanitize($token);

        expect($result)->toBe(str_repeat('*', 40));
    });

    it('sanitizes github fine-grained tokens', function () {
        // 22 chars + 59 chars = valid fine-grained token format
        $token = 'github_pat_1234567890123456789012_12345678901234567890123456789012345678901234567890123456789';
        $result = TokenSanitizer::sanitize($token);

        expect($result)->not->toContain('github_pat_')
            ->and($result)->toBe(str_repeat('*', strlen($token)));
    });

    it('sanitizes bearer tokens', function () {
        $content = 'Authorization: Bearer abc123xyz';
        $result = TokenSanitizer::sanitize($content);

        expect($result)->not->toContain('abc123xyz');
    });

    it('leaves non-sensitive content unchanged', function () {
        $content = 'Hello world, this is a test message';
        $result = TokenSanitizer::sanitize($content);

        expect($result)->toBe($content);
    });

    it('sanitizes arrays recursively', function () {
        $data = [
            'token' => 'ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            'nested' => [
                'secret' => 'ghp_yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy',
            ],
            'safe' => 'hello',
        ];

        $result = TokenSanitizer::sanitizeArray($data);

        expect($result['token'])->toBe(str_repeat('*', 40))
            ->and($result['nested']['secret'])->toBe(str_repeat('*', 40))
            ->and($result['safe'])->toBe('hello');
    });

    it('redacts sensitive headers', function () {
        $headers = [
            'Authorization' => 'Bearer secret123',
            'Content-Type' => 'application/json',
            'X-GitHub-Token' => 'ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        ];

        $result = TokenSanitizer::sanitizeHeaders($headers);

        expect($result['Authorization'])->toBe('[REDACTED]')
            ->and($result['Content-Type'])->toBe('application/json')
            ->and($result['X-GitHub-Token'])->toBe('[REDACTED]');
    });
});
