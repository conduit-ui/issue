<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Label;
use ConduitUI\Issue\Services\IssueLabelManager;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

describe('IssueLabelManager', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->manager = new IssueLabelManager($this->connector, 'owner/repo', 123);
    });

    it('lists all issue labels', function () {
        $this->mockClient->addResponse(MockResponse::make([
            ['id' => 1, 'name' => 'bug', 'color' => 'd73a4a', 'description' => 'Bug', 'default' => false],
            ['id' => 2, 'name' => 'feature', 'color' => '1d76db', 'description' => 'Feature', 'default' => false],
        ]));

        $labels = $this->manager->all();

        expect($labels)->toHaveCount(2)
            ->and($labels->first())->toBeInstanceOf(Label::class)
            ->and($labels->first()->name)->toBe('bug')
            ->and($labels->last()->name)->toBe('feature');
    });

    it('adds a single label to issue', function () {
        $this->mockClient->addResponse(MockResponse::make([
            ['id' => 1, 'name' => 'bug', 'color' => 'd73a4a', 'description' => 'Bug', 'default' => false],
        ]));

        $labels = $this->manager->add('bug');

        expect($labels)->toHaveCount(1)
            ->and($labels->first()->name)->toBe('bug');
    });

    it('adds multiple labels to issue', function () {
        $this->mockClient->addResponse(MockResponse::make([
            ['id' => 1, 'name' => 'bug', 'color' => 'd73a4a', 'description' => 'Bug', 'default' => false],
            ['id' => 2, 'name' => 'priority-high', 'color' => 'ff0000', 'description' => 'High priority', 'default' => false],
        ]));

        $labels = $this->manager->add(['bug', 'priority-high']);

        expect($labels)->toHaveCount(2)
            ->and($labels->first()->name)->toBe('bug')
            ->and($labels->last()->name)->toBe('priority-high');
    });

    it('removes a label from issue', function () {
        $this->mockClient->addResponse(MockResponse::make([], 204));

        $result = $this->manager->remove('bug');

        expect($result)->toBeTrue();
    });

    it('returns false when remove fails', function () {
        $this->mockClient->addResponse(MockResponse::make([], 404));

        $result = $this->manager->remove('nonexistent');

        expect($result)->toBeFalse();
    });

    it('replaces all labels', function () {
        $this->mockClient->addResponse(MockResponse::make([
            ['id' => 1, 'name' => 'feature', 'color' => '1d76db', 'description' => 'Feature', 'default' => false],
            ['id' => 2, 'name' => 'enhancement', 'color' => '0e8a16', 'description' => 'Enhancement', 'default' => false],
        ]));

        $labels = $this->manager->set(['feature', 'enhancement']);

        expect($labels)->toHaveCount(2)
            ->and($labels->first()->name)->toBe('feature')
            ->and($labels->last()->name)->toBe('enhancement');
    });

    it('clears all labels', function () {
        $this->mockClient->addResponse(MockResponse::make([], 204));

        $result = $this->manager->clear();

        expect($result)->toBeTrue();
    });

    it('returns false when clear fails', function () {
        $this->mockClient->addResponse(MockResponse::make([], 404));

        $result = $this->manager->clear();

        expect($result)->toBeFalse();
    });
});
