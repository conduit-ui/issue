<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Comments\DeleteCommentRequest;
use Saloon\Enums\Method;

describe('DeleteCommentRequest', function () {
    it('has correct method', function () {
        $request = new DeleteCommentRequest('owner', 'repo', 123);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::DELETE);
    });

    it('resolves correct endpoint', function () {
        $request = new DeleteCommentRequest('testowner', 'testrepo', 456);

        expect($request->resolveEndpoint())
            ->toBe('/repos/testowner/testrepo/issues/comments/456');
    });

    it('uses different comment ids', function () {
        $request1 = new DeleteCommentRequest('owner', 'repo', 100);
        $request2 = new DeleteCommentRequest('owner', 'repo', 200);

        expect($request1->resolveEndpoint())->toContain('100')
            ->and($request2->resolveEndpoint())->toContain('200');
    });
});
