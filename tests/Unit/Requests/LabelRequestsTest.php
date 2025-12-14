<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Labels\AddLabelsRequest;
use ConduitUI\Issue\Requests\Labels\RemoveAllLabelsRequest;
use ConduitUI\Issue\Requests\Labels\RemoveLabelRequest;
use ConduitUI\Issue\Requests\Labels\ReplaceAllLabelsRequest;
use Saloon\Enums\Method;

describe('AddLabelsRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new AddLabelsRequest('owner', 'repo', 123, ['bug', 'urgent']);

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/labels');
    });

    it('uses POST method', function () {
        $request = new AddLabelsRequest('owner', 'repo', 123, ['bug']);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::POST);
    });

    it('returns labels as body', function () {
        $labels = ['bug', 'urgent', 'help wanted'];
        $request = new AddLabelsRequest('owner', 'repo', 123, $labels);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');

        expect($method->invoke($request))->toBe(['labels' => $labels]);
    });
});

describe('RemoveLabelRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new RemoveLabelRequest('owner', 'repo', 123, 'bug');

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/labels/bug');
    });

    it('url encodes special characters in label', function () {
        $request = new RemoveLabelRequest('owner', 'repo', 123, 'help wanted');

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/labels/help%20wanted');
    });

    it('uses DELETE method', function () {
        $request = new RemoveLabelRequest('owner', 'repo', 123, 'bug');
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::DELETE);
    });
});

describe('RemoveAllLabelsRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new RemoveAllLabelsRequest('owner', 'repo', 123);

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/labels');
    });

    it('uses DELETE method', function () {
        $request = new RemoveAllLabelsRequest('owner', 'repo', 123);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::DELETE);
    });
});

describe('ReplaceAllLabelsRequest', function () {
    it('resolves endpoint correctly', function () {
        $request = new ReplaceAllLabelsRequest('owner', 'repo', 123, ['bug']);

        expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/labels');
    });

    it('uses PUT method', function () {
        $request = new ReplaceAllLabelsRequest('owner', 'repo', 123, ['bug']);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::PUT);
    });

    it('returns labels as body', function () {
        $labels = ['feature', 'documentation'];
        $request = new ReplaceAllLabelsRequest('owner', 'repo', 123, $labels);

        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('defaultBody');

        expect($method->invoke($request))->toBe(['labels' => $labels]);
    });
});
