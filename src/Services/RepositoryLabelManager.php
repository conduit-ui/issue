<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Label;
use ConduitUI\Issue\Requests\RepositoryLabels\CreateLabelRequest;
use ConduitUI\Issue\Requests\RepositoryLabels\DeleteLabelRequest;
use ConduitUI\Issue\Requests\RepositoryLabels\GetLabelRequest;
use ConduitUI\Issue\Requests\RepositoryLabels\ListLabelsRequest;
use ConduitUI\Issue\Requests\RepositoryLabels\UpdateLabelRequest;
use Illuminate\Support\Collection;

final class RepositoryLabelManager
{
    public function __construct(
        protected Connector $connector,
        protected string $fullName,
    ) {}

    /**
     * Get all repository labels
     *
     * @return Collection<int, Label>
     */
    public function all(): Collection
    {
        $response = $this->connector->send(
            new ListLabelsRequest($this->fullName)
        );

        return collect($response->json())
            ->map(fn (mixed $label): Label => Label::fromArray((array) $label));
    }

    /**
     * Get a specific label
     */
    public function find(string $name): Label
    {
        $response = $this->connector->send(
            new GetLabelRequest($this->fullName, $name)
        );

        return Label::fromArray($response->json());
    }

    /**
     * Create a new label
     */
    public function create(string $name, string $color, ?string $description = null): Label
    {
        $response = $this->connector->send(
            new CreateLabelRequest(
                $this->fullName,
                $name,
                ltrim($color, '#'),
                $description
            )
        );

        return Label::fromArray($response->json());
    }

    /**
     * Update an existing label
     */
    public function update(string $name, array $attributes): Label
    {
        if (isset($attributes['color'])) {
            $attributes['color'] = ltrim($attributes['color'], '#');
        }

        $response = $this->connector->send(
            new UpdateLabelRequest($this->fullName, $name, $attributes)
        );

        return Label::fromArray($response->json());
    }

    /**
     * Delete a label
     */
    public function delete(string $name): bool
    {
        $response = $this->connector->send(
            new DeleteLabelRequest($this->fullName, $name)
        );

        return $response->successful();
    }

    /**
     * Get a label builder for fluent creation
     */
    public function builder(): LabelBuilder
    {
        return new LabelBuilder($this->connector, $this->fullName);
    }

    /**
     * Sync labels from an array
     *
     * @param  array<int, array{name: string, color: string, description?: string}>  $labels
     * @return Collection<int, Label>
     */
    public function sync(array $labels): Collection
    {
        $existing = $this->all()->pluck('name')->toArray();
        $desired = collect($labels)->pluck('name')->toArray();

        // Delete labels not in desired list
        $toDelete = array_diff($existing, $desired);
        foreach ($toDelete as $name) {
            if (is_string($name)) {
                $this->delete($name);
            }
        }

        // Create or update labels
        $results = collect();
        foreach ($labels as $label) {
            if (in_array($label['name'], $existing)) {
                $results->push($this->update($label['name'], $label));
            } else {
                $results->push($this->create(
                    $label['name'],
                    $label['color'],
                    $label['description'] ?? null
                ));
            }
        }

        return $results;
    }
}
