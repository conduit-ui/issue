<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Events\ListIssueEventsRequest;
use ConduitUI\Issue\Requests\Events\ListIssueTimelineRequest;
use ConduitUI\Issue\Requests\Events\ListRepositoryIssueEventsRequest;
use Saloon\Enums\Method;

describe('Event Requests', function () {
    describe('ListIssueEventsRequest', function () {
        it('has correct HTTP method', function () {
            $request = new ListIssueEventsRequest('owner', 'repo', 123);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('resolves correct endpoint', function () {
            $request = new ListIssueEventsRequest('owner', 'repo', 123);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/events');
        });

        it('includes filters in query parameters', function () {
            $filters = ['per_page' => 50, 'page' => 2];
            $request = new ListIssueEventsRequest('owner', 'repo', 123, $filters);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);

            $query = $method->invoke($request);

            expect($query)->toBe($filters);
        });

        it('has empty filters by default', function () {
            $request = new ListIssueEventsRequest('owner', 'repo', 123);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);

            $query = $method->invoke($request);

            expect($query)->toBe([]);
        });
    });

    describe('ListIssueTimelineRequest', function () {
        it('has correct HTTP method', function () {
            $request = new ListIssueTimelineRequest('owner', 'repo', 123);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('resolves correct endpoint', function () {
            $request = new ListIssueTimelineRequest('owner', 'repo', 123);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/timeline');
        });

        it('includes timeline preview header', function () {
            $request = new ListIssueTimelineRequest('owner', 'repo', 123);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultHeaders');
            $method->setAccessible(true);

            $headers = $method->invoke($request);

            expect($headers)->toHaveKey('Accept');
            expect($headers['Accept'])->toBe('application/vnd.github.mockingbird-preview+json');
        });

        it('includes filters in query parameters', function () {
            $filters = ['per_page' => 100];
            $request = new ListIssueTimelineRequest('owner', 'repo', 123, $filters);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);

            $query = $method->invoke($request);

            expect($query)->toBe($filters);
        });
    });

    describe('ListRepositoryIssueEventsRequest', function () {
        it('has correct HTTP method', function () {
            $request = new ListRepositoryIssueEventsRequest('owner', 'repo');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('resolves correct endpoint', function () {
            $request = new ListRepositoryIssueEventsRequest('owner', 'repo');

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/events');
        });

        it('includes filters in query parameters', function () {
            $filters = ['per_page' => 30, 'page' => 1];
            $request = new ListRepositoryIssueEventsRequest('owner', 'repo', $filters);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);

            $query = $method->invoke($request);

            expect($query)->toBe($filters);
        });

        it('has empty filters by default', function () {
            $request = new ListRepositoryIssueEventsRequest('owner', 'repo');

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);

            $query = $method->invoke($request);

            expect($query)->toBe([]);
        });
    });
});
