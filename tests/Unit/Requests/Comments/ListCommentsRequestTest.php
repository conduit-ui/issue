<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Comments\ListCommentsRequest;
use Saloon\Enums\Method;

describe('ListCommentsRequest', function () {
    it('has correct method', function () {
        $request = new ListCommentsRequest('owner', 'repo', 123);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::GET);
    });

    it('resolves correct endpoint', function () {
        $request = new ListCommentsRequest('testowner', 'testrepo', 456);

        expect($request->resolveEndpoint())
            ->toBe('/repos/testowner/testrepo/issues/456/comments');
    });

    it('includes filters in query parameters', function () {
        $filters = ['per_page' => 50, 'page' => 2];
        $request = new ListCommentsRequest('owner', 'repo', 123, $filters);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultQuery');
        $method->setAccessible(true);
        $query = $method->invoke($request);

        expect($query)->toBe($filters);
    });

    it('handles empty filters', function () {
        $request = new ListCommentsRequest('owner', 'repo', 123);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultQuery');
        $method->setAccessible(true);
        $query = $method->invoke($request);

        expect($query)->toBeArray()->toBeEmpty();
    });
});
