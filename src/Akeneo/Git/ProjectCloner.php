<?php

namespace Akeneo\Git;

use Akeneo\Event\Events;
use Akeneo\System\Executor;
use Github\Api\Repo;
use Github\Client;
use Github\Exception\RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProjectCloner
{
    public function __construct(
        protected Client $client,
        protected Executor $executor,
        protected EventDispatcherInterface $eventDispatcher,
        protected string $fork_owner,
        protected string $owner,
        protected string $repository
    ) {
    }

    /**
     * Clone the remote repository in a local folder with a specific branch, and synchronize forked repository
     * to the main repository.
     */
    public function cloneProject(string $baseDir, string $baseBranch = null, bool $dryRun = false): string
    {
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        if (null === $baseBranch) {
            $baseBranch = 'master';
        }

        $projectDir = sprintf('%s%s%s', $baseDir, DIRECTORY_SEPARATOR, $this->repository);

        if (!is_dir($projectDir . '/.git')) {
            $this->cloneUpstream($projectDir);
        }

        $this->createBranch($baseBranch, $projectDir);

        $this->update($baseBranch, $projectDir, $dryRun);

        return $projectDir;
    }

    /**
     * Check if Github repository exists
     *
     * @throws \Exception
     */
    protected function validateRepository(): void
    {
        /** @var Repo $service */
        $service = $this->client->api('repo');
        try {
            $service->show($this->fork_owner, $this->repository);
        } catch (RuntimeException $exception) {
            throw new \Exception(
                sprintf(
                    "The fork repository %s/%s can not be found: %s",
                    $this->fork_owner,
                    $this->repository,
                    $exception->getMessage(),
                )
            );
        }
    }

    /**
     * Clone the forked repository and set upstream
     *
     * @param $projectDir
     *
     * @throws \Exception
     */
    protected function cloneUpstream($projectDir): void
    {
        $this->eventDispatcher->dispatch(
            new GenericEvent($this, [
                'fork_owner' => $this->fork_owner,
                'repository' => $this->repository,
                'project_dir' => $projectDir,
            ]),
            Events::PRE_GITHUB_CLONE,
        );

        $this->validateRepository();

        // Clone the forked repository
        $this->executor->execute(
            sprintf(
                'git clone git@github.com:%s/%s.git %s',
                $this->fork_owner,
                $this->repository,
                $projectDir
            )
        );

        // Set upstream to main repository
        $this->executor->execute(
            sprintf(
                'cd %s && git remote add upstream git@github.com:%s/%s.git',
                $projectDir,
                $this->owner,
                $this->repository
            )
        );
        $this->eventDispatcher->dispatch(new GenericEvent(), Events::POST_GITHUB_CLONE);
    }

    /**
     * Create new branch if not exists
     *
     * @param $baseBranch
     * @param $projectDir
     *
     * @throws \Exception
     */
    protected function createBranch($baseBranch, $projectDir): void
    {
        $this->eventDispatcher->dispatch(
            new GenericEvent($this, [
                'branch' => $baseBranch,
            ]),
            Events::PRE_GITHUB_SET_BRANCH,
        );

        // If the branch does not exist on the fork repository
        try {
            $this->executor->execute(
                sprintf('cd %s && git rev-parse --verify remotes/origin/%s', $projectDir, $baseBranch)
            );
        } catch (\Exception $e) {
            $this->executor->execute(
                sprintf(
                    'cd %s && git fetch upstream && git checkout %s && git push origin %s',
                    $projectDir,
                    $baseBranch,
                    $baseBranch
                )
            );
        }

        // Set branch
        $this->executor->execute(
            sprintf(
                'cd %s && git checkout --track -B %s origin/%s',
                $projectDir,
                $baseBranch,
                $baseBranch
            )
        );

        $this->eventDispatcher->dispatch(new GenericEvent(), Events::POST_GITHUB_SET_BRANCH);
    }

    /**
     * Update the forked repository with latest updates
     */
    protected function update(string $baseBranch, string $projectDir, bool $dryRun = false): void
    {
        $this->eventDispatcher->dispatch(
            new GenericEvent($this, [
                'owner' => $this->owner,
                'repository' => $this->repository,
            ]),
            Events::PRE_GITHUB_UPDATE,
        );

        // Pull last updates of the main repository
        $this->executor->execute(
            sprintf(
                'cd %s && git pull origin %s',
                $projectDir,
                $baseBranch
            )
        );

        // Checkout branch
        $this->executor->execute(
            sprintf(
                'cd %s && git checkout %s',
                $projectDir,
                $baseBranch
            )
        );

        // Fetch upstream
        $this->executor->execute(
            sprintf(
                'cd %s && git fetch upstream',
                $projectDir
            )
        );

        // Merge upstream
        $this->executor->execute(
            sprintf(
                'cd %s && git merge upstream/%s',
                $projectDir,
                $baseBranch
            )
        );

        // Push latest updates
        if (!$dryRun) {
            $this->executor->execute(
                sprintf(
                    'cd %s && git push origin %s',
                    $projectDir,
                    $baseBranch
                )
            );
        }

        $this->eventDispatcher->dispatch(new GenericEvent(), Events::POST_GITHUB_UPDATE);
    }
}
