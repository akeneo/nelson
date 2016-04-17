<?php


namespace Akeneo\Git;

use Akeneo\System\Executor;
use Github\Client;

/**
 * Class PullRequestCreator
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PullRequestCreator
{
    /** @var Executor */
    protected $executor;

    /** @var Client */
    protected $client;

    /** @var string */
    protected $fork_owner;

    /** @var string */
    protected $owner;

    /** @var string */
    protected $repository;

    /**
     * @param Executor $executor
     * @param Client   $client
     * @param string   $fork_owner
     * @param string   $owner
     * @param string   $repository
     */
    public function __construct(Executor $executor, Client $client, $fork_owner, $owner, $repository)
    {
        $this->executor   = $executor;
        $this->client     = $client;
        $this->fork_owner = $fork_owner;
        $this->owner      = $owner;
        $this->repository = $repository;
    }

    /**
     * @param string $baseBranch
     * @param string $baseDir
     * @param string $projectDir
     */
    public function create($baseBranch, $baseDir, $projectDir)
    {
        $branch = $baseBranch.'-'.(new \DateTime())->format('Y-m-d-H-i');
        $cmd = sprintf(
            'cd %s && ' .
            'git checkout -b crowdin/%s && ' .
            'git add .',
            $projectDir,
            $branch
        );
        $this->executor->execute($cmd);

        $cmd = sprintf(
            'cd %s git && git commit -m "[Crowdin] Updated translations" && git push origin crowdin/%s',
            $projectDir,
            $branch
        );
        $this->executor->execute($cmd);

        $this->client->api('pr')->create(
            $this->owner,
            $this->repository,
            [
                'head'  => sprintf('%s:crowdin/%s', $this->fork_owner, $branch),
                'base'  => $baseBranch,
                'title' => 'Update translations from Crowdin',
                'body'  => 'Updated on ' . $branch,
            ]
        );

        $cmd = sprintf('cd %s/ && rm -rf *.zip', $baseDir);
        $this->executor->execute($cmd);

        $cmd = sprintf(
            'cd %s && ' .
            'git checkout master',
            $projectDir
        );
        $this->executor->execute($cmd);
    }
}
