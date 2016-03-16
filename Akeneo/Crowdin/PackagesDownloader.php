<?php

namespace Akeneo\Crowdin;

use Crowdin\Api\Download;
use Crowdin\Api\Export;
use Crowdin\Client;

/**
 * Class PackagesDownloader
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PackagesDownloader
{
    /** @var Client */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
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

        /** @var Export $serviceExport */
        $serviceExport = $this->client->api('export');
        $serviceExport->setBranch($baseBranch);
        $serviceExport->execute();

        /** @var Download $serviceDownload */
        $serviceDownload = $this->client->api('download');
        $serviceDownload->setBranch($baseBranch);
        $download = $serviceDownload->setCopyDestination($baseDir);
        foreach ($locales as $locale) {
            $download->setPackage(sprintf('%s.zip', $locale))->execute();
        }
    }
}
