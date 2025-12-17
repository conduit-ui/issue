<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Reactions\CreateCommentReactionRequest;
use Saloon\Enums\Method;

describe('CreateCommentReactionRequest', function () {
    it('has correct method', function () {
        $request = new CreateCommentReactionRequest('owner', 'repo', 123, '+1');
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::POST);
    });

    it('resolves correct endpoint', function () {
        $request = new CreateCommentReactionRequest('testowner', 'testrepo', 456, 'heart');

        expect($request->resolveEndpoint())
            ->toBe('/repos/testowner/testrepo/issues/comments/456/reactions');
    });

    it('includes content in request body', function () {
        $request = new CreateCommentReactionRequest('owner', 'repo', 123, 'rocket');

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');
        $method->setAccessible(true);
        $body = $method->invoke($request);

        expect($body)->toHaveKey('content', 'rocket');
    });

    it('handles all valid reaction types', function () {
        $types = ['+1', '-1', 'laugh', 'confused', 'heart', 'hooray', 'rocket', 'eyes'];

        foreach ($types as $type) {
            $request = new CreateCommentReactionRequest('owner', 'repo', 123, $type);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body['content'])->toBe($type);
        }
    });
});
