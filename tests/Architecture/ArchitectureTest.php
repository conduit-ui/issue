<?php

declare(strict_types=1);

arch('requests extend saloon request')
    ->expect('ConduitUI\Issue\Requests')
    ->toExtend(Saloon\Http\Request::class);

arch('exceptions extend base exception')
    ->expect('ConduitUI\Issue\Exceptions')
    ->toExtend(Exception::class);

arch('readonly data classes')
    ->expect([
        'ConduitUI\Issue\Data\Issue',
        'ConduitUI\Issue\Data\IssueEvent',
        'ConduitUI\Issue\Data\Label',
        'ConduitUI\Issue\Data\Milestone',
        'ConduitUI\Issue\Data\Reaction',
        'ConduitUI\Issue\Data\TimelineEvent',
        'ConduitUI\Issue\Data\User',
    ])
    ->toBeReadonly();

// Comment is not readonly - it needs mutable context for fluent API (react, reactions, unreact)

arch('traits are traits')
    ->expect('ConduitUI\Issue\Traits')
    ->toBeTraits();

arch('contracts are interfaces')
    ->expect('ConduitUI\Issue\Contracts')
    ->toBeInterfaces();

arch('services use strict types')
    ->expect('ConduitUI\Issue\Services')
    ->toUseStrictTypes();

arch('no debugging statements')
    ->expect(['dd', 'dump', 'var_dump', 'print_r', 'ray'])
    ->not->toBeUsed();
