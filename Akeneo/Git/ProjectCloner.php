<?php

namespace Akeneo\Git;

use Akeneo\System\Executor;

/**
 * Class ProjectCloner
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProjectCloner
{
    /** @var Executor */
    protected $executor;

    /**
     * @param Executor $executor
     */
    public function __construct(Executor $executor)
    {
        $this->executor = $executor;
    }

    /**
     * @param string $username
     * @param string $baseDir
     * @param string $edition
     * @param string $baseBranch
     *
     * @return string
     */
    public function cloneProject($username, $baseDir, $edition, $baseBranch = 'master')
    {
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $projectDir = $baseDir . '/' . ucfirst($edition);

        if (!is_dir($projectDir . '/.git')) {
            $cmd = sprintf(
                'git clone git@github.com:%s/pim-%s-dev.git %s && ' .
                'cd %s && ' .
                'git remote add upstream git@github.com:akeneo/pim-%s-dev.git',
                $username,
                $edition,
                $projectDir,
                $projectDir,
                $edition
            );
            $this->executor->execute($cmd);
        }

        $cmd = sprintf(
            'cd %s && git checkout --track -b %s origin/%s',
            $projectDir,
            $baseBranch,
            $baseBranch
        );
        $this->executor->execute($cmd);

        $cmd = sprintf(
            'cd %s && git pull origin %s',
            $projectDir,
            $baseBranch
        );
        $this->executor->execute($cmd);

        $cmd = sprintf(
            'cd %s && git checkout %s && git fetch upstream && git merge upstream/%s && git push origin %s',
            $projectDir,
            $baseBranch,
            $baseBranch,
            $baseBranch
        );
        $this->executor->execute($cmd);

        return $projectDir;
    }
}