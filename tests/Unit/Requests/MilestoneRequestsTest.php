<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Milestones\CreateMilestoneRequest;
use ConduitUI\Issue\Requests\Milestones\DeleteMilestoneRequest;
use ConduitUI\Issue\Requests\Milestones\GetMilestoneRequest;
use ConduitUI\Issue\Requests\Milestones\ListMilestonesRequest;
use ConduitUI\Issue\Requests\Milestones\UpdateMilestoneRequest;
use Saloon\Enums\Method;

describe('GetMilestoneRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new GetMilestoneRequest('owner', 'repo', 1);

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/milestones/1');
    });

    it('uses GET method', function () {
        $request = new GetMilestoneRequest('owner', 'repo', 1);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::GET);
    });
});

describe('ListMilestonesRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new ListMilestonesRequest('owner', 'repo');

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/milestones');
    });

    it('uses GET method', function () {
        $request = new ListMilestonesRequest('owner', 'repo');
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::GET);
    });

    it('returns filters as query parameters', function () {
        $filters = ['state' => 'open', 'sort' => 'due_on', 'direction' => 'asc'];
        $request = new ListMilestonesRequest('owner', 'repo', $filters);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultQuery');

        expect($method->invoke($request))->toBe($filters);
    });

    it('returns empty array when no filters', function () {
        $request = new ListMilestonesRequest('owner', 'repo');

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultQuery');

        expect($method->invoke($request))->toBe([]);
    });
});

describe('CreateMilestoneRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new CreateMilestoneRequest('owner', 'repo', ['title' => 'v1.0.0']);

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/milestones');
    });

    it('uses POST method', function () {
        $request = new CreateMilestoneRequest('owner', 'repo', ['title' => 'v1.0.0']);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::POST);
    });

    it('returns data as body', function () {
        $data = [
            'title' => 'v1.0.0',
            'description' => 'First release',
            'due_on' => '2024-12-31T23:59:59Z',
        ];
        $request = new CreateMilestoneRequest('owner', 'repo', $data);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');

        expect($method->invoke($request))->toBe($data);
    });
});

describe('UpdateMilestoneRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new UpdateMilestoneRequest('owner', 'repo', 1, ['state' => 'closed']);

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/milestones/1');
    });

    it('uses PATCH method', function () {
        $request = new UpdateMilestoneRequest('owner', 'repo', 1, ['state' => 'closed']);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::PATCH);
    });

    it('returns data as body', function () {
        $data = [
            'title' => 'v1.0.1',
            'state' => 'closed',
            'description' => 'Updated description',
        ];
        $request = new UpdateMilestoneRequest('owner', 'repo', 1, $data);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');

        expect($method->invoke($request))->toBe($data);
    });
});

describe('DeleteMilestoneRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new DeleteMilestoneRequest('owner', 'repo', 1);

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/milestones/1');
    });

    it('uses DELETE method', function () {
        $request = new DeleteMilestoneRequest('owner', 'repo', 1);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::DELETE);
    });
});
