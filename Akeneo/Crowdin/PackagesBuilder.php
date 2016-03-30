<?php

namespace Akeneo\Crowdin;

/**
 * Class PackagesBuilder
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PackagesBuilder
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
     * Force the build/refresh of the package on Crowdin by calling the export api
     */
    public function build()
    {
        $this->client->api('export')->execute();
    }
}
