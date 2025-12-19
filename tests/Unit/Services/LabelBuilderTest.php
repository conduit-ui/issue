<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Label;
use ConduitUI\Issue\Services\LabelBuilder;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

describe('LabelBuilder', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->builder = new LabelBuilder($this->connector, 'owner/repo');
    });

    it('creates label with fluent API', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'id' => 123,
            'name' => 'priority-high',
            'color' => 'd73a4a',
            'description' => 'High priority issue',
            'default' => false,
        ]));

        $label = $this->builder
            ->name('priority-high')
            ->color('d73a4a')
            ->description('High priority issue')
            ->create();

        expect($label)->toBeInstanceOf(Label::class)
            ->and($label->name)->toBe('priority-high')
            ->and($label->color)->toBe('d73a4a')
            ->and($label->description)->toBe('High priority issue');
    });

    it('strips hash from color', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'id' => 123,
            'name' => 'test',
            'color' => 'ff0000',
            'description' => null,
            'default' => false,
        ]));

        $label = $this->builder
            ->name('test')
            ->color('#ff0000')
            ->create();

        expect($label->color)->toBe('ff0000');
    });

    it('uses red color preset', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'id' => 123,
            'name' => 'bug',
            'color' => 'd73a4a',
            'description' => null,
            'default' => false,
        ]));

        $label = $this->builder
            ->name('bug')
            ->red()
            ->create();

        expect($label->color)->toBe('d73a4a');
    });

    it('uses orange color preset', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'id' => 123,
            'name' => 'warning',
            'color' => 'd4a72c',
            'description' => null,
            'default' => false,
        ]));

        $label = $this->builder
            ->name('warning')
            ->orange()
            ->create();

        expect($label->color)->toBe('d4a72c');
    });

    it('uses yellow color preset', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'id' => 123,
            'name' => 'attention',
            'color' => 'fef2c0',
            'description' => null,
            'default' => false,
        ]));

        $label = $this->builder
            ->name('attention')
            ->yellow()
            ->create();

        expect($label->color)->toBe('fef2c0');
    });

    it('uses green color preset', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'id' => 123,
            'name' => 'enhancement',
            'color' => '0e8a16',
            'description' => null,
            'default' => false,
        ]));

        $label = $this->builder
            ->name('enhancement')
            ->green()
            ->create();

        expect($label->color)->toBe('0e8a16');
    });

    it('uses blue color preset', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'id' => 123,
            'name' => 'documentation',
            'color' => '1d76db',
            'description' => null,
            'default' => false,
        ]));

        $label = $this->builder
            ->name('documentation')
            ->blue()
            ->create();

        expect($label->color)->toBe('1d76db');
    });

    it('uses purple color preset', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'id' => 123,
            'name' => 'question',
            'color' => '5319e7',
            'description' => null,
            'default' => false,
        ]));

        $label = $this->builder
            ->name('question')
            ->purple()
            ->create();

        expect($label->color)->toBe('5319e7');
    });

    it('uses pink color preset', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'id' => 123,
            'name' => 'design',
            'color' => 'e99695',
            'description' => null,
            'default' => false,
        ]));

        $label = $this->builder
            ->name('design')
            ->pink()
            ->create();

        expect($label->color)->toBe('e99695');
    });

    it('uses gray color preset', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'id' => 123,
            'name' => 'wontfix',
            'color' => 'd1d5da',
            'description' => null,
            'default' => false,
        ]));

        $label = $this->builder
            ->name('wontfix')
            ->gray()
            ->create();

        expect($label->color)->toBe('d1d5da');
    });

    it('chains multiple method calls', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'id' => 123,
            'name' => 'feature',
            'color' => '1d76db',
            'description' => 'New feature request',
            'default' => false,
        ]));

        $label = $this->builder
            ->name('feature')
            ->blue()
            ->description('New feature request')
            ->create();

        expect($label->name)->toBe('feature')
            ->and($label->color)->toBe('1d76db')
            ->and($label->description)->toBe('New feature request');
    });
});
