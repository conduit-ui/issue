<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Reactions\ListCommentReactionsRequest;
use Saloon\Enums\Method;

describe('ListCommentReactionsRequest', function () {
    it('has correct method', function () {
        $request = new ListCommentReactionsRequest('owner', 'repo', 123);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::GET);
    });

    it('resolves correct endpoint', function () {
        $request = new ListCommentReactionsRequest('testowner', 'testrepo', 456);

        expect($request->resolveEndpoint())
            ->toBe('/repos/testowner/testrepo/issues/comments/456/reactions');
    });

    it('includes filters in query', function () {
        $request = new ListCommentReactionsRequest('owner', 'repo', 123, ['content' => '+1', 'per_page' => 30]);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultQuery');
        $method->setAccessible(true);
        $query = $method->invoke($request);

        expect($query)->toHaveKey('content', '+1')
            ->and($query)->toHaveKey('per_page', 30);
    });

    it('returns empty query when no filters', function () {
        $request = new ListCommentReactionsRequest('owner', 'repo', 123);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultQuery');
        $method->setAccessible(true);
        $query = $method->invoke($request);

        expect($query)->toBe([]);
    });
});
