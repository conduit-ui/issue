<?php

declare(strict_types=1);

use ConduitUI\Issue\Requests\Reactions\DeleteCommentReactionRequest;
use Saloon\Enums\Method;

describe('DeleteCommentReactionRequest', function () {
    it('has correct method', function () {
        $request = new DeleteCommentReactionRequest('owner', 'repo', 123, 456);
        $reflection = new ReflectionClass($request);
        $property = $reflection->getProperty('method');

        expect($property->getValue($request))->toBe(Method::DELETE);
    });

    it('resolves correct endpoint', function () {
        $request = new DeleteCommentReactionRequest('testowner', 'testrepo', 123, 789);

        expect($request->resolveEndpoint())
            ->toBe('/repos/testowner/testrepo/issues/comments/123/reactions/789');
    });

    it('uses correct comment id in endpoint', function () {
        $request = new DeleteCommentReactionRequest('owner', 'repo', 999, 111);

        expect($request->resolveEndpoint())
            ->toBe('/repos/owner/repo/issues/comments/999/reactions/111');
    });

    it('uses correct reaction id in endpoint', function () {
        $request = new DeleteCommentReactionRequest('owner', 'repo', 123, 777);

        expect($request->resolveEndpoint())
            ->toBe('/repos/owner/repo/issues/comments/123/reactions/777');
    });
});
