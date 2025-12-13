<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

describe('Package configuration', function () {
    it('has default config values', function () {
        $config = Config::get('github-issues');

        expect($config)->toBeArray()
            ->and($config)->toHaveKey('token')
            ->and($config)->toHaveKey('base_url')
            ->and($config)->toHaveKey('timeout')
            ->and($config)->toHaveKey('retry')
            ->and($config)->toHaveKey('cache');
    });

    it('has retry configuration', function () {
        $retry = Config::get('github-issues.retry');

        expect($retry)->toBeArray()
            ->and($retry)->toHaveKey('times')
            ->and($retry)->toHaveKey('sleep');
    });

    it('has cache configuration', function () {
        $cache = Config::get('github-issues.cache');

        expect($cache)->toBeArray()
            ->and($cache)->toHaveKey('enabled')
            ->and($cache)->toHaveKey('ttl')
            ->and($cache)->toHaveKey('prefix');
    });

    it('uses environment variables for sensitive values', function () {
        $token = Config::get('github-issues.token');

        // Token should be null by default (from env)
        expect($token)->toBeNull();
    });
});
