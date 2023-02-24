<?php

namespace Akeneo\Crowdin;

use Akeneo\Crowdin\Api\AddFile;
use Akeneo\Event\Events;
use Akeneo\Nelson\TargetResolver;
use Akeneo\Nelson\TranslationFile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This class creates all the missing files of a Crowdin project.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationFilesCreator
{
    const MAX_UPLOAD = 10;

    public function __construct(
        protected Client $client,
        protected EventDispatcherInterface $eventDispatcher,
        protected TargetResolver $targetResolver
    ) {
    }

    /**
     * Create the missing files in Crowdin. Set it by batch using MAX_UPLOAD const.
     *
     * @param TranslationFile[]      $files
     * @param TranslationProjectInfo $projectInfo
     */
    public function create(
        array $files,
        TranslationProjectInfo $projectInfo,
        string $baseBranch,
        bool $dryRun = false
    ): void {
        $this->eventDispatcher->dispatch(new Event(), Events::PRE_CROWDIN_CREATE_FILES);

        $existingFiles = $projectInfo->getExistingFiles($baseBranch);
        $fileSets = array_chunk($this->filterExistingFiles($files, $existingFiles), self::MAX_UPLOAD);

        foreach ($fileSets as $fileSet) {
            /** @var AddFile $service */
            $service = $this->client->api('add-file');
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
                        'source' => $file->getSource(),
                        'dry_run' => $dryRun,
                    ]),
                    Events::CROWDIN_CREATE_FILE,
                );
            }
            if (null !== $service->getTranslations() && count($service->getTranslations()) > 0) {
                $service->execute();
            }
        }

        $this->eventDispatcher->dispatch(new Event(), Events::POST_CROWDIN_CREATE_FILES);
    }

    /**
     * @param TranslationFile[] $files
     * @param string[]          $existingFiles
     *
     * @return TranslationFile[]
     */
    protected function filterExistingFiles(array $files, array $existingFiles): array
    {
        $result = [];

        foreach ($files as $file) {
            if (!in_array(
                $this->targetResolver->getTarget(
                    $file->getProjectDir(),
                    $file->getSource()
                ),
                $existingFiles
            )) {
                $result[] = $file;
            }
        }

        return $result;
    }
}
