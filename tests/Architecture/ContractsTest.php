<?php

declare(strict_types=1);

use ConduitUI\Issue\Contracts\CommentManagerInterface;
use ConduitUI\Issue\Contracts\IssueBuilderInterface;
use ConduitUI\Issue\Contracts\IssueManagerInterface;
use ConduitUI\Issue\Contracts\IssueQueryInterface;
use ConduitUI\Issue\Contracts\IssuesFacadeInterface;
use ConduitUI\Issue\Contracts\LabelBuilderInterface;
use ConduitUI\Issue\Contracts\LabelManagerInterface;
use ConduitUI\Issue\Contracts\MilestoneBuilderInterface;
use ConduitUI\Issue\Contracts\MilestoneManagerInterface;
use ConduitUI\Issue\Contracts\MilestoneQueryInterface;
use ConduitUI\Issue\Contracts\ReactionManagerInterface;
use ConduitUI\Issue\Contracts\RepositoryLabelManagerInterface;

arch('all contracts are interfaces')
    ->expect('ConduitUI\Issue\Contracts')
    ->toBeInterfaces();

arch('contracts have Interface suffix')
    ->expect('ConduitUI\Issue\Contracts')
    ->toHaveSuffix('Interface');

describe('Query Contracts', function () {
    it('IssueQueryInterface has required filtering methods', function () {
        $reflection = new ReflectionClass(IssueQueryInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        expect($methods)->toContain('state')
            ->and($methods)->toContain('labels')
            ->and($methods)->toContain('assignee')
            ->and($methods)->toContain('creator')
            ->and($methods)->toContain('mentioned')
            ->and($methods)->toContain('since')
            ->and($methods)->toContain('sort')
            ->and($methods)->toContain('direction')
            ->and($methods)->toContain('perPage')
            ->and($methods)->toContain('page')
            ->and($methods)->toContain('get')
            ->and($methods)->toContain('first')
            ->and($methods)->toContain('count');
    });

    it('MilestoneQueryInterface has required filtering methods', function () {
        $reflection = new ReflectionClass(MilestoneQueryInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        expect($methods)->toContain('state')
            ->and($methods)->toContain('sort')
            ->and($methods)->toContain('direction')
            ->and($methods)->toContain('perPage')
            ->and($methods)->toContain('page')
            ->and($methods)->toContain('get')
            ->and($methods)->toContain('first')
            ->and($methods)->toContain('count');
    });
});

describe('Manager Contracts', function () {
    it('IssueManagerInterface has required methods', function () {
        $reflection = new ReflectionClass(IssueManagerInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        expect($methods)->toContain('find')
            ->and($methods)->toContain('query')
            ->and($methods)->toContain('create')
            ->and($methods)->toContain('update')
            ->and($methods)->toContain('close')
            ->and($methods)->toContain('reopen')
            ->and($methods)->toContain('lock')
            ->and($methods)->toContain('unlock');
    });

    it('CommentManagerInterface has required methods', function () {
        $reflection = new ReflectionClass(CommentManagerInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        expect($methods)->toContain('create')
            ->and($methods)->toContain('update')
            ->and($methods)->toContain('delete')
            ->and($methods)->toContain('list')
            ->and($methods)->toContain('get');
    });

    it('LabelManagerInterface has required methods', function () {
        $reflection = new ReflectionClass(LabelManagerInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        expect($methods)->toContain('add')
            ->and($methods)->toContain('remove')
            ->and($methods)->toContain('replace')
            ->and($methods)->toContain('clear');
    });

    it('ReactionManagerInterface has required methods', function () {
        $reflection = new ReflectionClass(ReactionManagerInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        expect($methods)->toContain('create')
            ->and($methods)->toContain('delete')
            ->and($methods)->toContain('list')
            ->and($methods)->toContain('thumbsUp')
            ->and($methods)->toContain('thumbsDown')
            ->and($methods)->toContain('laugh')
            ->and($methods)->toContain('hooray')
            ->and($methods)->toContain('confused')
            ->and($methods)->toContain('heart')
            ->and($methods)->toContain('rocket')
            ->and($methods)->toContain('eyes');
    });

    it('MilestoneManagerInterface has required methods', function () {
        $reflection = new ReflectionClass(MilestoneManagerInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        expect($methods)->toContain('find')
            ->and($methods)->toContain('query')
            ->and($methods)->toContain('create')
            ->and($methods)->toContain('update')
            ->and($methods)->toContain('delete')
            ->and($methods)->toContain('close')
            ->and($methods)->toContain('reopen');
    });

    it('RepositoryLabelManagerInterface has required methods', function () {
        $reflection = new ReflectionClass(RepositoryLabelManagerInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        expect($methods)->toContain('list')
            ->and($methods)->toContain('get')
            ->and($methods)->toContain('create')
            ->and($methods)->toContain('update')
            ->and($methods)->toContain('delete');
    });
});

describe('Facade Contract', function () {
    it('IssuesFacadeInterface provides unified interface', function () {
        $reflection = new ReflectionClass(IssuesFacadeInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        expect($methods)->toContain('issues')
            ->and($methods)->toContain('comments')
            ->and($methods)->toContain('labels')
            ->and($methods)->toContain('reactions')
            ->and($methods)->toContain('milestones');
    });
});

describe('Contract Return Types', function () {
    it('query contracts return Collection from get()', function () {
        $queryReflection = new ReflectionClass(IssueQueryInterface::class);
        $getMethod = $queryReflection->getMethod('get');
        $returnType = $getMethod->getReturnType();

        expect($returnType)->not->toBeNull()
            ->and($returnType->getName())->toBe('Illuminate\Support\Collection');
    });

    it('query contracts return nullable data from first()', function () {
        $queryReflection = new ReflectionClass(IssueQueryInterface::class);
        $firstMethod = $queryReflection->getMethod('first');
        $returnType = $firstMethod->getReturnType();

        expect($returnType)->not->toBeNull()
            ->and($returnType->allowsNull())->toBeTrue();
    });

    it('query contracts return int from count()', function () {
        $queryReflection = new ReflectionClass(IssueQueryInterface::class);
        $countMethod = $queryReflection->getMethod('count');
        $returnType = $countMethod->getReturnType();

        expect($returnType)->not->toBeNull()
            ->and($returnType->getName())->toBe('int');
    });
});

describe('Contract Method Signatures', function () {
    it('query methods are chainable', function () {
        $reflection = new ReflectionClass(IssueQueryInterface::class);
        $stateMethod = $reflection->getMethod('state');
        $returnType = $stateMethod->getReturnType();

        expect($returnType)->not->toBeNull()
            ->and($returnType->getName())->toBe('self');
    });

    it('manager create methods return data objects', function () {
        $reflection = new ReflectionClass(IssueManagerInterface::class);
        $createMethod = $reflection->getMethod('create');
        $returnType = $createMethod->getReturnType();

        expect($returnType)->not->toBeNull()
            ->and($returnType->getName())->toBe('ConduitUI\Issue\Data\Issue');
    });
});

describe('Builder Contracts', function () {
    it('IssueBuilderInterface has required methods', function () {
        $reflection = new ReflectionClass(IssueBuilderInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        expect($methods)->toContain('title')
            ->and($methods)->toContain('body')
            ->and($methods)->toContain('assignees')
            ->and($methods)->toContain('assignee')
            ->and($methods)->toContain('labels')
            ->and($methods)->toContain('label')
            ->and($methods)->toContain('milestone')
            ->and($methods)->toContain('create')
            ->and($methods)->toContain('toArray');
    });

    it('LabelBuilderInterface has required methods', function () {
        $reflection = new ReflectionClass(LabelBuilderInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        expect($methods)->toContain('name')
            ->and($methods)->toContain('color')
            ->and($methods)->toContain('description')
            ->and($methods)->toContain('create')
            ->and($methods)->toContain('toArray');
    });

    it('MilestoneBuilderInterface has required methods', function () {
        $reflection = new ReflectionClass(MilestoneBuilderInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        expect($methods)->toContain('title')
            ->and($methods)->toContain('state')
            ->and($methods)->toContain('description')
            ->and($methods)->toContain('dueOn')
            ->and($methods)->toContain('create')
            ->and($methods)->toContain('toArray');
    });

    it('builder methods are chainable', function () {
        $reflection = new ReflectionClass(IssueBuilderInterface::class);
        $titleMethod = $reflection->getMethod('title');
        $returnType = $titleMethod->getReturnType();

        expect($returnType)->not->toBeNull()
            ->and($returnType->getName())->toBe('self');
    });
});
