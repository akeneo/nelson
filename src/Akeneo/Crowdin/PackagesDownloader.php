<?php

namespace Akeneo\Crowdin;

use Akeneo\Crowdin\Api\Download;
use Akeneo\Crowdin\Api\Export;
use Akeneo\Event\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class PackagesDownloader
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PackagesDownloader
{
    /** @var Client */
    protected $client;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param Client                   $client
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Client $client, EventDispatcherInterface $eventDispatcher)
    {
        $this->client = $client;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Download an archive with the translations for the specified branch.
     *
     * @param string[] $locales
     * @param string   $baseDir
     * @param string   $baseBranch
     */
    public function download(array $locales, $baseDir, $baseBranch)
    {
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $this->export($baseBranch);

        $this->downloadPackages($locales, $baseDir, $baseBranch);
    }

    /**
     * Export a Crowdin package (build it)
     *
     * @param string $baseBranch
     */
    protected function export($baseBranch)
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
     *
     * @param array  $locales
     * @param string $baseDir
     * @param string $baseBranch
     */
    protected function downloadPackages(array $locales, $baseDir, $baseBranch)
    {
        $this->eventDispatcher->dispatch(new Event(), Events::PRE_CROWDIN_DOWNLOAD);

        /** @var Download $serviceDownload */
        $serviceDownload = $this->client->api('download');
        $serviceDownload->setBranch($baseBranch);
        $serviceDownload = $serviceDownload->setCopyDestination($baseDir);

        foreach ($locales as $locale) {
            $this->eventDispatcher->dispatch(
                new GenericEvent($this, ['locale' => $locale,]),
                Events::CROWDIN_DOWNLOAD
            );
            $serviceDownload->setPackage(sprintf('%s.zip', $locale))->execute();
        }

        $this->eventDispatcher->dispatch(new Event(), Events::POST_CROWDIN_DOWNLOAD);
    }
}
