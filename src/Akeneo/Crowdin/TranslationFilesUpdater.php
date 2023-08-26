<?php

namespace Akeneo\Crowdin;

use Akeneo\Crowdin\Api\UpdateFile;
use Akeneo\Event\Events;
use Akeneo\Nelson\TargetResolver;
use Akeneo\Nelson\TranslationFile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class TranslationFilesUpdater
 *
 * @see       https://crowdin.com/page/api/update-file limited to 20 files maximum per call
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationFilesUpdater
{
    const MAX_NB_FILES = 20;

    public function __construct(
        protected Client $client,
        protected EventDispatcherInterface $eventDispatcher,
        protected TargetResolver $targetResolver
    ) {
    }

    /**
     * @param TranslationFile[] $files
     */
    public function update(array $files, string $baseBranch, bool $dryRun = false): void
    {
        $this->eventDispatcher->dispatch(new Event(), Events::PRE_CROWDIN_UPDATE_FILES);

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

                $this->eventDispatcher->dispatch(
                    new GenericEvent($this, [
                        'target' => $target,
                        'dry_run' => $dryRun,
                    ]),
                    Events::CROWDIN_UPDATE_FILE,
                );
            }
            if (null !== $service->getTranslations() && count($service->getTranslations()) > 0) {
                $service->execute();
            }
        }

        $this->eventDispatcher->dispatch(new Event(), Events::POST_CROWDIN_UPDATE_FILES);
    }
}
