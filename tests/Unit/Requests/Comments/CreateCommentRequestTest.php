<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Comments\CreateCommentRequest;
use Saloon\Enums\Method;

describe('CreateCommentRequest', function () {
    it('has correct method', function () {
        $request = new CreateCommentRequest('owner', 'repo', 123, 'Test comment');
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::POST);
    });

    it('resolves correct endpoint', function () {
        $request = new CreateCommentRequest('testowner', 'testrepo', 456, 'Test comment');

        expect($request->resolveEndpoint())
            ->toBe('/repos/testowner/testrepo/issues/456/comments');
    });

    it('includes body in request body', function () {
        $request = new CreateCommentRequest('owner', 'repo', 123, 'This is a new comment');

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');
        $method->setAccessible(true);
        $body = $method->invoke($request);

        expect($body)->toHaveKey('body', 'This is a new comment');
    });

    it('handles multiline comment body', function () {
        $multilineBody = "First line\nSecond line\nThird line";
        $request = new CreateCommentRequest('owner', 'repo', 123, $multilineBody);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');
        $method->setAccessible(true);
        $body = $method->invoke($request);

        expect($body['body'])->toBe($multilineBody);
    });
});
