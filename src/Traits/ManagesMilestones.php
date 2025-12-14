<?php

declare(strict_types=1);

namespace ConduitUI\Issue\Traits;

use ConduitUI\Issue\Data\Milestone;
use ConduitUI\Issue\Requests\Milestones\CreateMilestoneRequest;
use ConduitUI\Issue\Requests\Milestones\DeleteMilestoneRequest;
use ConduitUI\Issue\Requests\Milestones\GetMilestoneRequest;
use ConduitUI\Issue\Requests\Milestones\ListMilestonesRequest;
use ConduitUI\Issue\Requests\Milestones\UpdateMilestoneRequest;
use Illuminate\Support\Collection;

trait ManagesMilestones
{
    use HandlesApiErrors;
    use ValidatesInput;

    /**
     * @return \Illuminate\Support\Collection<int, \ConduitUI\Issue\Data\Milestone>
     */
    public function listMilestones(string $owner, string $repo, array $filters = []): Collection
    {
        $this->validateRepository($owner, $repo);

        $response = $this->connector->send(
            new ListMilestonesRequest($owner, $repo, $filters)
        );

        $this->handleApiResponse($response, $owner, $repo);

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return collect($items)
            ->map(fn (array $data): Milestone => Milestone::fromArray($data));
    }

    public function getMilestone(string $owner, string $repo, int $milestoneNumber): Milestone
    {
        $this->validateRepository($owner, $repo);
        $this->validateMilestoneNumber($milestoneNumber);

        $response = $this->connector->send(
            new GetMilestoneRequest($owner, $repo, $milestoneNumber)
        );

        $this->handleApiResponse($response, $owner, $repo);

        return Milestone::fromArray($response->json());
    }

    public function createMilestone(string $owner, string $repo, array $data): Milestone
    {
        $this->validateRepository($owner, $repo);
        $sanitizedData = $this->validateMilestoneData($data);

        $response = $this->connector->send(
            new CreateMilestoneRequest($owner, $repo, $sanitizedData)
        );

        $this->handleApiResponse($response, $owner, $repo);

        return Milestone::fromArray($response->json());
    }

    public function updateMilestone(string $owner, string $repo, int $milestoneNumber, array $data): Milestone
    {
        $this->validateRepository($owner, $repo);
        $this->validateMilestoneNumber($milestoneNumber);
        $sanitizedData = $this->validateMilestoneData($data);

        $response = $this->connector->send(
            new UpdateMilestoneRequest($owner, $repo, $milestoneNumber, $sanitizedData)
        );

        $this->handleApiResponse($response, $owner, $repo);

        return Milestone::fromArray($response->json());
    }

    public function deleteMilestone(string $owner, string $repo, int $milestoneNumber): bool
    {
        $this->validateRepository($owner, $repo);
        $this->validateMilestoneNumber($milestoneNumber);

        $response = $this->connector->send(
            new DeleteMilestoneRequest($owner, $repo, $milestoneNumber)
        );

        $this->handleApiResponse($response, $owner, $repo);

        return $response->status() === 204;
    }

    public function closeMilestone(string $owner, string $repo, int $milestoneNumber): Milestone
    {
        return $this->updateMilestone($owner, $repo, $milestoneNumber, ['state' => 'closed']);
    }

    public function reopenMilestone(string $owner, string $repo, int $milestoneNumber): Milestone
    {
        return $this->updateMilestone($owner, $repo, $milestoneNumber, ['state' => 'open']);
    }
}
