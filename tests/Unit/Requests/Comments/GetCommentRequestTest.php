<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Comments\GetCommentRequest;
use Saloon\Enums\Method;

describe('GetCommentRequest', function () {
    it('has correct method', function () {
        $request = new GetCommentRequest('owner', 'repo', 123);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::GET);
    });

    it('resolves correct endpoint', function () {
        $request = new GetCommentRequest('testowner', 'testrepo', 789);

        expect($request->resolveEndpoint())
            ->toBe('/repos/testowner/testrepo/issues/comments/789');
    });

    it('uses different comment ids', function () {
        $request1 = new GetCommentRequest('owner', 'repo', 100);
        $request2 = new GetCommentRequest('owner', 'repo', 200);

        expect($request1->resolveEndpoint())->toContain('100')
            ->and($request2->resolveEndpoint())->toContain('200');
    });
});
