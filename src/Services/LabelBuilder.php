<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Label;
use ConduitUI\Issue\Requests\RepositoryLabels\CreateLabelRequest;

final class LabelBuilder
{
    protected ?string $name = null;

    protected ?string $color = null;

    protected ?string $description = null;

    public function __construct(
        protected Connector $connector,
        protected string $fullName,
    ) {}

    /**
     * Set label name
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set label color
     */
    public function color(string $color): self
    {
        $this->color = ltrim($color, '#');

        return $this;
    }

    /**
     * Set label description
     */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Use a predefined color
     */
    public function red(): self
    {
        return $this->color('d73a4a');
    }

    public function orange(): self
    {
        return $this->color('d4a72c');
    }

    public function yellow(): self
    {
        return $this->color('fef2c0');
    }

    public function green(): self
    {
        return $this->color('0e8a16');
    }

    public function blue(): self
    {
        return $this->color('1d76db');
    }

    public function purple(): self
    {
        return $this->color('5319e7');
    }

    public function pink(): self
    {
        return $this->color('e99695');
    }

    public function gray(): self
    {
        return $this->color('d1d5da');
    }

    /**
     * Create the label
     */
    public function create(): Label
    {
        if ($this->name === null || $this->color === null) {
            throw new \InvalidArgumentException('Name and color are required to create a label');
        }

        $response = $this->connector->send(
            new CreateLabelRequest(
                $this->fullName,
                $this->name,
                $this->color,
                $this->description
            )
        );

        return Label::fromArray($response->json());
    }
}
