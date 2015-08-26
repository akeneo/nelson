<?php

namespace Akeneo\Crowdin;

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
     * @param array  $packages
     * @param string $baseDir
     */
    public function download(array $packages, $baseDir)
    {
        $this->client->api('export')->execute();
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $download = $this->client->api('download')->setCopyDestination($baseDir);
        foreach ($packages as $package) {
            $download->setPackage(sprintf('%s.zip', $package))->execute();
        }
    }
}
