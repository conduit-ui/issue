<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Services\IssuesService;

describe('IssuesService composition', function () {
    it('uses all traits', function () {
        $traits = class_uses_recursive(IssuesService::class);

        expect($traits)->toContain('ConduitUI\Issue\Traits\ManagesIssues')
            ->and($traits)->toContain('ConduitUI\Issue\Traits\ManagesIssueLabels')
            ->and($traits)->toContain('ConduitUI\Issue\Traits\ManagesIssueAssignees');
    });

    it('has connector property', function () {
        $reflection = new ReflectionClass(IssuesService::class);
        $constructor = $reflection->getConstructor();

        expect($constructor)->not->toBeNull();

        $parameters = $constructor->getParameters();
        expect($parameters)->toHaveCount(1);

        $connectorParam = $parameters[0];
        expect($connectorParam->getName())->toBe('connector')
            ->and($connectorParam->getType()->getName())->toBe(Connector::class);
    });

    it('implements issues service interface', function () {
        expect(IssuesService::class)
            ->toImplement('ConduitUI\Issue\Contracts\IssuesServiceInterface');
    });
});
