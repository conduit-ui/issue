<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Issues\CreateIssueRequest;
use ConduitUI\Issue\Requests\Issues\GetIssueRequest;
use ConduitUI\Issue\Requests\Issues\ListIssuesRequest;
use ConduitUI\Issue\Requests\Issues\UpdateIssueRequest;
use Saloon\Enums\Method;

describe('GetIssueRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new GetIssueRequest('owner', 'repo', 123);

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123');
    });

    it('uses GET method', function () {
        $request = new GetIssueRequest('owner', 'repo', 123);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::GET);
    });
});

describe('ListIssuesRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new ListIssuesRequest('owner', 'repo');

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues');
    });

    it('uses GET method', function () {
        $request = new ListIssuesRequest('owner', 'repo');
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::GET);
    });

    it('returns filters as query parameters', function () {
        $filters = ['state' => 'open', 'labels' => 'bug'];
        $request = new ListIssuesRequest('owner', 'repo', $filters);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultQuery');

        expect($method->invoke($request))->toBe($filters);
    });

    it('returns empty array when no filters', function () {
        $request = new ListIssuesRequest('owner', 'repo');

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultQuery');

        expect($method->invoke($request))->toBe([]);
    });
});

describe('CreateIssueRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new CreateIssueRequest('owner', 'repo', ['title' => 'Test']);

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues');
    });

    it('uses POST method', function () {
        $request = new CreateIssueRequest('owner', 'repo', ['title' => 'Test']);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::POST);
    });

    it('returns data as body', function () {
        $data = ['title' => 'Test Issue', 'body' => 'Description'];
        $request = new CreateIssueRequest('owner', 'repo', $data);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');

        expect($method->invoke($request))->toBe($data);
    });
});

describe('UpdateIssueRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new UpdateIssueRequest('owner', 'repo', 123, ['state' => 'closed']);

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123');
    });

    it('uses PATCH method', function () {
        $request = new UpdateIssueRequest('owner', 'repo', 123, ['state' => 'closed']);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::PATCH);
    });

    it('returns data as body', function () {
        $data = ['state' => 'closed', 'state_reason' => 'completed'];
        $request = new UpdateIssueRequest('owner', 'repo', 123, $data);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');

        expect($method->invoke($request))->toBe($data);
    });
});
