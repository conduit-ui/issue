<?php

declare(strict_types=1);

use ConduitUI\Issue\Security\TokenSanitizer;

describe('TokenSanitizer edge cases', function () {
    describe('sanitize', function () {
        it('sanitizes gho tokens', function () {
            $content = 'Token: gho_abcdefghijklmnopqrstuvwxyz1234567890';

            expect(TokenSanitizer::sanitize($content))->toBe('Token: ****************************************');
        });

        it('sanitizes ghu tokens', function () {
            $content = 'Token: ghu_abcdefghijklmnopqrstuvwxyz1234567890';

            expect(TokenSanitizer::sanitize($content))->toBe('Token: ****************************************');
        });

        it('sanitizes ghs tokens', function () {
            $content = 'Token: ghs_abcdefghijklmnopqrstuvwxyz1234567890';

            expect(TokenSanitizer::sanitize($content))->toBe('Token: ****************************************');
        });

        it('sanitizes ghr tokens', function () {
            $content = 'Token: ghr_abcdefghijklmnopqrstuvwxyz1234567890';

            expect(TokenSanitizer::sanitize($content))->toBe('Token: ****************************************');
        });

        it('sanitizes long hex strings', function () {
            $hexKey = str_repeat('a', 40);
            $content = "API Key: {$hexKey}";

            expect(TokenSanitizer::sanitize($content))->toBe('API Key: ****************************************');
        });

        it('does not sanitize short hex strings', function () {
            $content = 'Short: abcdef123456';

            expect(TokenSanitizer::sanitize($content))->toBe('Short: abcdef123456');
        });

        it('handles multiple tokens in one string', function () {
            $content = 'Token1: ghp_abcdefghijklmnopqrstuvwxyz1234567890 Token2: Bearer abc123';

            $result = TokenSanitizer::sanitize($content);

            expect($result)->not->toContain('ghp_')
                ->and($result)->not->toContain('Bearer abc');
        });
    });

    describe('sanitizeArray', function () {
        it('handles non-string non-array values', function () {
            $data = [
                'count' => 123,
                'active' => true,
                'rate' => 1.5,
                'nothing' => null,
            ];

            $result = TokenSanitizer::sanitizeArray($data);

            expect($result)->toBe($data);
        });

        it('handles deeply nested arrays', function () {
            $data = [
                'level1' => [
                    'level2' => [
                        'token' => 'ghp_abcdefghijklmnopqrstuvwxyz1234567890',
                    ],
                ],
            ];

            $result = TokenSanitizer::sanitizeArray($data);

            expect($result['level1']['level2']['token'])->toBe('****************************************');
        });
    });

    describe('sanitizeHeaders', function () {
        it('redacts x-github-token header', function () {
            $headers = ['X-GitHub-Token' => 'secret-token'];

            $result = TokenSanitizer::sanitizeHeaders($headers);

            expect($result['X-GitHub-Token'])->toBe('[REDACTED]');
        });

        it('redacts x-access-token header', function () {
            $headers = ['X-Access-Token' => 'secret-token'];

            $result = TokenSanitizer::sanitizeHeaders($headers);

            expect($result['X-Access-Token'])->toBe('[REDACTED]');
        });

        it('handles non-string non-array header values', function () {
            $headers = [
                'Content-Length' => 1234,
                'X-Custom' => true,
            ];

            $result = TokenSanitizer::sanitizeHeaders($headers);

            expect($result['Content-Length'])->toBe(1234)
                ->and($result['X-Custom'])->toBe(true);
        });

        it('sanitizes array header values', function () {
            $headers = [
                'X-Tokens' => ['ghp_abcdefghijklmnopqrstuvwxyz1234567890'],
            ];

            $result = TokenSanitizer::sanitizeHeaders($headers);

            expect($result['X-Tokens'][0])->toBe('****************************************');
        });

        it('is case insensitive for sensitive headers', function () {
            $headers = [
                'AUTHORIZATION' => 'Bearer token',
                'authorization' => 'Bearer token2',
                'Authorization' => 'Bearer token3',
            ];

            $result = TokenSanitizer::sanitizeHeaders($headers);

            expect($result['AUTHORIZATION'])->toBe('[REDACTED]')
                ->and($result['authorization'])->toBe('[REDACTED]')
                ->and($result['Authorization'])->toBe('[REDACTED]');
        });
    });
});
