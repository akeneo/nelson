<?php

namespace Akeneo\Git;

use Akeneo\System\Executor;
use Github\Api\Repo;
use Github\Client;
use Github\Exception\RuntimeException;

/**
 * Class ProjectCloner
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProjectCloner
{
    /** @var Client */
    protected $client;

    /** @var Executor */
    protected $executor;

    /** @var string */
    protected $fork_owner;

    /** @var string */
    protected $owner;

    /** @var string */
    protected $repository;

    /**
     * @param Client   $client
     * @param Executor $executor
     * @param string   $fork_owner
     * @param string   $owner
     * @param string   $repository
     */
    public function __construct(Client $client, Executor $executor, $fork_owner, $owner, $repository)
    {
        $this->client     = $client;
        $this->executor   = $executor;
        $this->fork_owner = $fork_owner;
        $this->owner      = $owner;
        $this->repository = $repository;
    }

    /**
     * Clone the remote repository in a local folder with a specific branch, and synchronize forked repository
     * to the main repository.
     *
     * @param string $baseDir
     * @param string $baseBranch
     *
     * @return string
     */
    public function cloneProject($baseDir, $baseBranch = 'master')
    {
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $projectDir = sprintf('%s%s%s', $baseDir, DIRECTORY_SEPARATOR, $this->repository);

        if (!is_dir($projectDir . '/.git')) {
            /** @var Repo $service */
            $service = $this->client->api('repo');
            try {
                $service->show($this->fork_owner, $this->repository);
            } catch (RuntimeException $exception) {
                throw new \Exception(sprintf(
                    "The fork repository %s/%s can not be found.\n".
                    "Please manually create this repository before continue.",
                    $this->fork_owner,
                    $this->repository
                ));
            }

            // Clone the forked repository
            $this->executor->execute(sprintf(
                'git clone git@github.com:%s/%s.git %s',
                $this->fork_owner,
                $this->repository,
                $projectDir
            ));

            // Set upstream to main repository
            $this->executor->execute(sprintf(
                'cd %s && git remote add upstream git@github.com:%s/%s.git',
                $projectDir,
                $this->owner,
                $this->repository
            ));
        }

        // Set branch
        $this->executor->execute(sprintf(
            'cd %s && git checkout --track -b %s origin/%s',
            $projectDir,
            $baseBranch,
            $baseBranch
        ));

        // Pull last updates of the main repository
        $this->executor->execute(sprintf(
            'cd %s && git pull origin %s',
            $projectDir,
            $baseBranch
        ));

        // Push last updates to forked repository
        $this->executor->execute(sprintf(
            'cd %s && git checkout %s && git fetch upstream && git merge upstream/%s && git push origin %s',
            $projectDir,
            $baseBranch,
            $baseBranch,
            $baseBranch
        ));

        return $projectDir;
    }
}
