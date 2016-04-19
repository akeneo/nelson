<?php

namespace Akeneo\Crowdin;

use Akeneo\Event\Events;
use Akeneo\System\TargetResolver;
use Akeneo\System\TranslationFile;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class TranslationFilesUpdater
 *
 * @see https://crowdin.com/page/api/update-file limited to 20 files maximum per call
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationFilesUpdater
{
    /** @var int */
    const MAX_NB_FILES = 20;

    /** @var Client */
    protected $client;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var TargetResolver */
    protected $targetResolver;

    /**
     * @param Client                   $client
     * @param EventDispatcherInterface $eventDispatcher
     * @param TargetResolver           $targetResolver
     */
    public function __construct(
        Client $client,
        EventDispatcherInterface $eventDispatcher,
        TargetResolver $targetResolver
    ) {
        $this->client          = $client;
        $this->eventDispatcher = $eventDispatcher;
        $this->targetResolver  = $targetResolver;
    }

    /**
     * @param TranslationFile[] $files
     * @param string            $baseBranch
     */
    public function update(array $files, $baseBranch)
    {
        $this->eventDispatcher->dispatch(Events::PRE_CROWDIN_UPDATE_FILES);

        $fileSets = array_chunk($files, self::MAX_NB_FILES);

        foreach ($fileSets as $fileSet) {
            $service = $this->client->api('update-file');
            $service->setBranch($baseBranch);

            foreach ($fileSet as $file) {
                /** @var TranslationFile $file */
                $target = $this->targetResolver->getTarget(
                    $file->getProjectDir(),
                    $file->getSource()
                );

                $this->eventDispatcher->dispatch(Events::CROWDIN_UPDATE_FILE);

                $service->addTranslation($file->getSource(), $target, $file->getPattern());
            }
            $service->execute();
        }

        $this->eventDispatcher->dispatch(Events::POST_CROWDIN_UPDATE_FILES);
    }
}
