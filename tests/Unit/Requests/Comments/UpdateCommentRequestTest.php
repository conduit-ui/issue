<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Comments\UpdateCommentRequest;
use Saloon\Enums\Method;

describe('UpdateCommentRequest', function () {
    it('has correct method', function () {
        $request = new UpdateCommentRequest('owner', 'repo', 123, 'Updated comment');
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::PATCH);
    });

    it('resolves correct endpoint', function () {
        $request = new UpdateCommentRequest('testowner', 'testrepo', 789, 'Updated comment');

        expect($request->resolveEndpoint())
            ->toBe('/repos/testowner/testrepo/issues/comments/789');
    });

    it('includes body in request body', function () {
        $request = new UpdateCommentRequest('owner', 'repo', 123, 'This is an updated comment');

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');
        $method->setAccessible(true);
        $body = $method->invoke($request);

        expect($body)->toHaveKey('body', 'This is an updated comment');
    });

    it('handles multiline comment body', function () {
        $multilineBody = "Updated first line\nUpdated second line";
        $request = new UpdateCommentRequest('owner', 'repo', 123, $multilineBody);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');
        $method->setAccessible(true);
        $body = $method->invoke($request);

        expect($body['body'])->toBe($multilineBody);
    });
});
