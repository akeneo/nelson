<?php

declare(strict_types=1);

namespace Akeneo\Crowdin;

use CrowdinApiClient\Crowdin;
use CrowdinApiClient\Model\Branch;
use CrowdinApiClient\Model\Progress;
use CrowdinApiClient\Model\Project;
use CrowdinApiClient\ModelCollection;

class NelsonClient
{
    /** @var Crowdin */
    private $crowdinClient;
    /** @var string */
    private $projectIdentifier;

    /** @var int|null */
    private $projectId = null;
    /** @var array|null */
    private $branchIds = null;

    public function __construct(Crowdin $crowdinClient, string $projectIdentifier)
    {
        $this->crowdinClient = $crowdinClient;
        $this->projectIdentifier = $projectIdentifier;
    }

    public function projectId(): int
    {
        if (null !== $this->projectId) {
            return $this->projectId;
        }

        $projects = $this->crowdinClient->project->list();

        /** @var Project $project */
        foreach ($projects->getIterator() as $project) {
            if ($project->getIdentifier() === $this->projectIdentifier) {
                $this->projectId = $project->getId();

                return $project->getId();
            }
        }

        throw new \InvalidArgumentException('Project not found');
    }

    public function branchIds(): array
    {
        if (null !== $this->branchIds) {
            return $this->branchIds;
        }

        $branches = $this->crowdinClient->branch->list($this->projectId());

        $ids = [];
        /** @var Branch $branch */
        foreach ($branches as $branch) {
            $ids[$branch->getName()] = $branch->getId();
        }

        return $ids;
    }

    /**
     * @return ModelCollection|Progress[]|null
     */
    public function translationProgress(?string $branchName = null): iterable
    {
        if (null === $branchName) {
            return $this->crowdinClient->translationStatus->getProjectProgress(
                $this->projectId(),
                ['limit' => 100]
            );
        }
        $branchId = $this->branchIds()[$branchName];

        return $this->crowdinClient->translationStatus->getBranchProgress(
            $this->projectId(),
            $branchId,
            ['limit' => 100]
        );
    }
}
