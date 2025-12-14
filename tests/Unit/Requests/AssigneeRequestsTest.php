<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Assignees\AddAssigneesRequest;
use ConduitUI\Issue\Requests\Assignees\RemoveAssigneesRequest;
use Saloon\Enums\Method;

describe('AddAssigneesRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new AddAssigneesRequest('owner', 'repo', 123, ['user1', 'user2']);

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/assignees');
    });

    it('uses POST method', function () {
        $request = new AddAssigneesRequest('owner', 'repo', 123, ['user1']);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::POST);
    });

    it('returns assignees as body', function () {
        $assignees = ['user1', 'user2', 'user3'];
        $request = new AddAssigneesRequest('owner', 'repo', 123, $assignees);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');

        expect($method->invoke($request))->toBe(['assignees' => $assignees]);
    });
});

describe('RemoveAssigneesRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new RemoveAssigneesRequest('owner', 'repo', 123, ['user1']);

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/assignees');
    });

    it('uses DELETE method', function () {
        $request = new RemoveAssigneesRequest('owner', 'repo', 123, ['user1']);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::DELETE);
    });

    it('returns assignees as body', function () {
        $assignees = ['user1', 'user2'];
        $request = new RemoveAssigneesRequest('owner', 'repo', 123, $assignees);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');

        expect($method->invoke($request))->toBe(['assignees' => $assignees]);
    });
});
