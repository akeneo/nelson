<?php

namespace Akeneo\Crowdin;

use Akeneo\Crowdin\Api\UpdateFile;
use Akeneo\Event\Events;
use Akeneo\Nelson\TargetResolver;
use Akeneo\Nelson\TranslationFile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

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
     */
    public function update(array $files, string $baseBranch, bool $dryRun = false): void
    {
        $this->eventDispatcher->dispatch(Events::PRE_CROWDIN_UPDATE_FILES);

        $fileSets = array_chunk($files, self::MAX_NB_FILES);

        foreach ($fileSets as $fileSet) {
            /** @var UpdateFile $service */
            $service = $this->client->api('update-file');
            $service->setBranch($baseBranch);

            foreach ($fileSet as $file) {
                /** @var TranslationFile $file */
                $target = $this->targetResolver->getTarget(
                    $file->getProjectDir(),
                    $file->getSource()
                );
                if (!$dryRun) {
                    $service->addTranslation($file->getSource(), $target, $file->getPattern());
                }

                $this->eventDispatcher->dispatch(Events::CROWDIN_UPDATE_FILE, new GenericEvent($this, [
                    'target'  => $target,
                    'dry_run' => $dryRun,
                ]));
            }
            if (null !== $service->getTranslations() && count($service->getTranslations()) > 0) {
                $service->execute();
            }
        }

        $this->eventDispatcher->dispatch(Events::POST_CROWDIN_UPDATE_FILES);
    }
}
