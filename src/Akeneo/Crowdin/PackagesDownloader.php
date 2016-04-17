<?php

namespace Akeneo\Crowdin;

use Akeneo\Crowdin\Api\Download;
use Akeneo\Crowdin\Api\Export;

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
        $serviceDownload = $serviceDownload->setCopyDestination($baseDir);
        foreach ($locales as $locale) {
            $serviceDownload->setPackage(sprintf('%s.zip', $locale))->execute();
        }
    }
}
