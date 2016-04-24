<?php


namespace Akeneo\Git;

use Akeneo\Event\Events;
use Akeneo\System\Executor;
use Github\Client;
use Github\Exception\ValidationFailedException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

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

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $fork_owner;

    /** @var string */
    protected $owner;

    /** @var string */
    protected $repository;

    /**
     * @param Executor                 $executor
     * @param Client                   $client
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $fork_owner
     * @param string                   $owner
     * @param string                   $repository
     */
    public function __construct(
        Executor $executor,
        Client $client,
        EventDispatcherInterface $eventDispatcher,
        $fork_owner,
        $owner,
        $repository
    ) {
        $this->executor        = $executor;
        $this->client          = $client;
        $this->eventDispatcher = $eventDispatcher;
        $this->fork_owner      = $fork_owner;
        $this->owner           = $owner;
        $this->repository      = $repository;
    }

    /**
     * Create a new Pull Request
     *
     * @param string $baseBranch
     * @param string $baseDir
     * @param string $projectDir
     */
    public function create($baseBranch, $baseDir, $projectDir)
    {
        $branch = $baseBranch.'-'.(new \DateTime())->format('Y-m-d-H-i');

        $this->eventDispatcher->dispatch(Events::PRE_GITHUB_CREATE_PR, new GenericEvent($this, [
            'name'   => $branch,
            'branch' => $baseBranch
        ]));

        $this->executor->execute(sprintf('cd %s && git checkout -B crowdin/%s', $projectDir, $branch));

        $this->executor->execute(sprintf('cd %s && git add .', $projectDir));

        $this->executor->execute(sprintf('cd %s && git commit -m "[Crowdin] Updated translations"', $projectDir));

        $this->executor->execute(sprintf('cd %s && git push origin crowdin/%s', $projectDir, $branch));

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

        $this->executor->execute(sprintf('cd %s/ && rm -rf *.zip', $baseDir));

        $this->executor->execute(sprintf('cd %s && ' . 'git checkout master', $projectDir));

        $this->eventDispatcher->dispatch(Events::POST_GITHUB_CREATE_PR);
    }

    /**
     * Check if current repository have diff, to know if we have to create PR or not.
     *
     * @param string $projectDir
     *
     * @return bool
     */
    public function haveDiff($projectDir)
    {
        $this->eventDispatcher->dispatch(Events::PRE_GITHUB_CHECK_DIFF);

        $result = $this->executor->execute(sprintf('cd %s && git diff|wc -l', $projectDir), true);
        $matches = null;
        preg_match('/^(?P<diff>\d+)\\n$/', $result[0], $matches);
        $diff = intval($matches['diff']);

        $this->eventDispatcher->dispatch(Events::POST_GITHUB_CHECK_DIFF, new GenericEvent($this, [
            'diff' => $diff
        ]));

        return intval(0 !== $diff);
    }
}
