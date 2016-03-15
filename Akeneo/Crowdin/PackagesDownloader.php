<?php

namespace Akeneo\Crowdin;

use Crowdin\Api\Download;
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
     * Download an archive with the translations. If basBranch is set, download only the specified branch.
     *
     * @param string[]    $locales
     * @param string      $baseDir
     * @param string|null $baseBranch
     */
    public function download(array $locales, $baseDir, $baseBranch = null)
    {
        $this->client->api('export')->execute();
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        /** @var Download $service */
        $service = $this->client->api('download');
        if (null !== $baseBranch) {
            $service->setBranch($baseBranch);
        }
        $download = $service->setCopyDestination($baseDir);
        foreach ($locales as $locale) {
            $download->setPackage(sprintf('%s.zip', $locale))->execute();
        }
    }
}
