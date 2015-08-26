<?php


namespace Akeneo\Git;

use Akeneo\System\Executor;
use Github\Client;

/**
 * Class PullRequestCreator
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PullRequestCreator
{
    /** @var Executor */
    protected $executor;

    /** @var Client */
    protected $client;

    /**
     * @param Executor $executor
     * @param Client   $client
     */
    public function __construct(Executor $executor, Client $client)
    {
        $this->executor = $executor;
        $this->client   = $client;
    }

    /**
     * @param string $baseBranch
     * @param string $baseDir
     * @param string $projectDir
     * @param string $edition
     * @param string $username
     */
    public function create($baseBranch, $baseDir, $projectDir, $edition, $username)
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
            'akeneo',
            'pim-'.$edition.'-dev',
            [
                'head' => sprintf('%s:crowdin/%s', $username, $branch),
                'base' => $baseBranch,
                'title' => 'Update translations from Crowdin',
                'body' => 'Updated on ' . $branch,
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