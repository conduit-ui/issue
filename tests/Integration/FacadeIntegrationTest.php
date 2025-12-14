<?php

declare(strict_types=1);

use ConduitUI\Issue\Facades\GithubIssues;
use ConduitUI\Issue\Services\IssuesService;

describe('GithubIssues Facade', function () {
    it('resolves to issues service', function () {
        $service = GithubIssues::getFacadeRoot();

        expect($service)->toBeInstanceOf(IssuesService::class);
    });

    it('has facade accessor', function () {
        $reflection = new ReflectionClass(GithubIssues::class);
        $method = $reflection->getMethod('getFacadeAccessor');
        $method->setAccessible(true);
        $accessor = $method->invoke(null);

        expect($accessor)->toBe(IssuesService::class);
    });

    it('can access service methods through facade', function () {
        expect(method_exists(IssuesService::class, 'listIssues'))->toBeTrue()
            ->and(method_exists(IssuesService::class, 'getIssue'))->toBeTrue()
            ->and(method_exists(IssuesService::class, 'createIssue'))->toBeTrue()
            ->and(method_exists(IssuesService::class, 'updateIssue'))->toBeTrue()
            ->and(method_exists(IssuesService::class, 'closeIssue'))->toBeTrue()
            ->and(method_exists(IssuesService::class, 'reopenIssue'))->toBeTrue()
            ->and(method_exists(IssuesService::class, 'addLabels'))->toBeTrue()
            ->and(method_exists(IssuesService::class, 'removeLabels'))->toBeTrue()
            ->and(method_exists(IssuesService::class, 'addAssignees'))->toBeTrue()
            ->and(method_exists(IssuesService::class, 'removeAssignees'))->toBeTrue();
    });
});
