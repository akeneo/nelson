<?php

namespace Akeneo\Crowdin;

use Akeneo\Crowdin\Api\Download;
use Akeneo\Crowdin\Api\Export;
use Akeneo\Event\Events;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PackagesDownloader
{
    public function __construct(protected Client $client, protected EventDispatcherInterface $eventDispatcher)
    {
    }

    /**
     * Download an archive with the translations for the specified branch.
     *
     * @param string[] $locales
     */
    public function download(array $locales, string $baseDir, string $baseBranch): void
    {
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $this->export($baseBranch);

        $this->downloadPackages($locales, $baseDir, $baseBranch);
    }

    /**
     * Export a Crowdin package (build it)
     */
    protected function export(string $baseBranch): void
    {
        $this->eventDispatcher->dispatch(new Event(), Events::PRE_CROWDIN_EXPORT);

        /** @var Export $serviceExport */
        $serviceExport = $this->client->api('export');
        $serviceExport->setBranch($baseBranch);
        $serviceExport->execute();

        $this->eventDispatcher->dispatch(new Event(), Events::POST_CROWDIN_EXPORT);
    }

    /**
     * Download a set of packages from a locales list
     */
    protected function downloadPackages(array $locales, string $baseDir, string $baseBranch): void
    {
        $this->eventDispatcher->dispatch(new Event(), Events::PRE_CROWDIN_DOWNLOAD);

        /** @var Download $serviceDownload */
        $serviceDownload = $this->client->api('download');
        $serviceDownload->setBranch($baseBranch);
        $serviceDownload = $serviceDownload->setCopyDestination($baseDir);

        foreach ($locales as $locale) {
            $this->eventDispatcher->dispatch(
                new GenericEvent($this, [
                    'locale' => $locale,
                ]),
                Events::CROWDIN_DOWNLOAD
            );
            $serviceDownload->setPackage(sprintf('%s.zip', $locale))->execute();
        }

        $this->eventDispatcher->dispatch(new Event(), Events::POST_CROWDIN_DOWNLOAD);
    }
}
