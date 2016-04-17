<?php

namespace Akeneo\Crowdin;

/**
 * Class PackagesBuilder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
