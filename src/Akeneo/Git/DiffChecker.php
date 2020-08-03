<?php

namespace Akeneo\Git;

use Akeneo\Event\Events;
use Akeneo\System\Executor;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class DiffChecker
{
    /** @var Executor */
    private $executor;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        Executor $executor,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->executor = $executor;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Check if current repository has diff, so we know if we have to create PR or not.
     *
     * @param string $projectDir
     *
     * @return bool
     * @throws Exception
     */
    public function haveDiff($projectDir)
    {
        $this->eventDispatcher->dispatch(new GenericEvent(), Events::PRE_GITHUB_CHECK_DIFF);

        $commands = [
            sprintf('cd %s && git diff|wc -l', $projectDir),
            sprintf('cd %s && git ls-files --others --exclude-standard|wc -l', $projectDir),
        ];
        $diff = 0;
        foreach ($commands as $command) {
            $result = $this->executor->execute($command, true);
            $matches = null;
            preg_match('/^(?P<diff>\d+)\\n$/', $result[0], $matches);
            $diff += intval($matches['diff']);
        }

        $this->eventDispatcher->dispatch(
            new GenericEvent($this, [
                'diff' => $diff,
            ]),
            Events::POST_GITHUB_CHECK_DIFF
        );

        return intval(0 !== $diff);
    }
}
