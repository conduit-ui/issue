<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Label;
use ConduitUI\Issue\Services\LabelBuilder;
use ConduitUI\Issue\Services\RepositoryLabelManager;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function labelResponse(array $override = []): array
{
    return array_merge([
        'id' => 123,
        'name' => 'bug',
        'color' => 'd73a4a',
        'description' => 'Something is broken',
        'default' => false,
    ], $override);
}

describe('RepositoryLabelManager', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->connector = new Connector('fake-token');
        $this->connector->withMockClient($this->mockClient);
        $this->manager = new RepositoryLabelManager($this->connector, 'owner/repo');
    });

    it('lists all repository labels', function () {
        $this->mockClient->addResponse(MockResponse::make([
            labelResponse(['id' => 1, 'name' => 'bug', 'color' => 'd73a4a']),
            labelResponse(['id' => 2, 'name' => 'feature', 'color' => '1d76db']),
        ]));

        $labels = $this->manager->all();

        expect($labels)->toHaveCount(2)
            ->and($labels->first())->toBeInstanceOf(Label::class)
            ->and($labels->first()->name)->toBe('bug')
            ->and($labels->last()->name)->toBe('feature');
    });

    it('finds a specific label', function () {
        $this->mockClient->addResponse(MockResponse::make(
            labelResponse(['name' => 'enhancement'])
        ));

        $label = $this->manager->find('enhancement');

        expect($label)->toBeInstanceOf(Label::class)
            ->and($label->name)->toBe('enhancement');
    });

    it('creates a new label', function () {
        $this->mockClient->addResponse(MockResponse::make(
            labelResponse(['name' => 'priority-high', 'color' => 'ff0000', 'description' => 'High priority'])
        ));

        $label = $this->manager->create('priority-high', 'ff0000', 'High priority');

        expect($label)->toBeInstanceOf(Label::class)
            ->and($label->name)->toBe('priority-high')
            ->and($label->color)->toBe('ff0000')
            ->and($label->description)->toBe('High priority');
    });

    it('creates a label and strips hash from color', function () {
        $this->mockClient->addResponse(MockResponse::make(
            labelResponse(['name' => 'test', 'color' => 'ff0000'])
        ));

        $label = $this->manager->create('test', '#ff0000');

        expect($label->color)->toBe('ff0000');
    });

    it('updates an existing label', function () {
        $this->mockClient->addResponse(MockResponse::make(
            labelResponse(['name' => 'bug', 'color' => '00ff00', 'description' => 'Updated'])
        ));

        $label = $this->manager->update('bug', [
            'color' => '00ff00',
            'description' => 'Updated',
        ]);

        expect($label)->toBeInstanceOf(Label::class)
            ->and($label->color)->toBe('00ff00')
            ->and($label->description)->toBe('Updated');
    });

    it('updates label and strips hash from color', function () {
        $this->mockClient->addResponse(MockResponse::make(
            labelResponse(['color' => 'ff0000'])
        ));

        $label = $this->manager->update('bug', ['color' => '#ff0000']);

        expect($label->color)->toBe('ff0000');
    });

    it('deletes a label', function () {
        $this->mockClient->addResponse(MockResponse::make([], 204));

        $result = $this->manager->delete('old-label');

        expect($result)->toBeTrue();
    });

    it('returns false when delete fails', function () {
        $this->mockClient->addResponse(MockResponse::make([], 404));

        $result = $this->manager->delete('nonexistent');

        expect($result)->toBeFalse();
    });

    it('returns a label builder', function () {
        $builder = $this->manager->builder();

        expect($builder)->toBeInstanceOf(LabelBuilder::class);
    });

    it('syncs labels by creating new ones', function () {
        // First call: get all existing labels (empty)
        $this->mockClient->addResponse(MockResponse::make([]));

        // Second call: create 'bug' label
        $this->mockClient->addResponse(MockResponse::make(
            labelResponse(['name' => 'bug', 'color' => 'd73a4a'])
        ));

        // Third call: create 'feature' label
        $this->mockClient->addResponse(MockResponse::make(
            labelResponse(['name' => 'feature', 'color' => '1d76db'])
        ));

        $labels = $this->manager->sync([
            ['name' => 'bug', 'color' => 'd73a4a', 'description' => 'Bug'],
            ['name' => 'feature', 'color' => '1d76db', 'description' => 'Feature'],
        ]);

        expect($labels)->toHaveCount(2);
    });

    it('syncs labels by updating existing ones', function () {
        // First call: get all existing labels
        $this->mockClient->addResponse(MockResponse::make([
            labelResponse(['name' => 'bug', 'color' => 'ff0000']),
        ]));

        // Second call: update 'bug' label
        $this->mockClient->addResponse(MockResponse::make(
            labelResponse(['name' => 'bug', 'color' => 'd73a4a'])
        ));

        $labels = $this->manager->sync([
            ['name' => 'bug', 'color' => 'd73a4a', 'description' => 'Updated bug'],
        ]);

        expect($labels)->toHaveCount(1)
            ->and($labels->first()->color)->toBe('d73a4a');
    });

    it('syncs labels by deleting removed ones', function () {
        // First call: get all existing labels
        $this->mockClient->addResponse(MockResponse::make([
            labelResponse(['name' => 'bug']),
            labelResponse(['name' => 'old-label']),
        ]));

        // Second call: delete 'old-label'
        $this->mockClient->addResponse(MockResponse::make([], 204));

        // Third call: update 'bug' label
        $this->mockClient->addResponse(MockResponse::make(
            labelResponse(['name' => 'bug'])
        ));

        $labels = $this->manager->sync([
            ['name' => 'bug', 'color' => 'd73a4a', 'description' => 'Bug'],
        ]);

        expect($labels)->toHaveCount(1);
    });
});
