<?php

namespace Akeneo\Crowdin;

use Crowdin\Client;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TranslatedProgressSelector
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class TranslatedProgressSelector
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
     * Display the packages to import
     *
     * @param OutputInterface $output
     * @param int             $minTranslatedProgress
     */
    public function display(OutputInterface $output, $minTranslatedProgress)
    {
        $codesToImport = $this->packages($minTranslatedProgress);
        foreach ($codesToImport as $code) {
            $output->write($code, true);
        }
    }

    /**
     * Return the list of packages to import
     *
     * @param int $minTranslatedProgress
     *
     * @return array
     */
    public function packages($minTranslatedProgress)
    {
        $response = $this->client->api('status')->execute();
        $xml = simplexml_load_string($response);
        $codesToImport = [];

        foreach($xml as $xmlElement) {
            $translated_progress = (int) $xmlElement->translated_progress;
            if ($translated_progress >= $minTranslatedProgress) {
                $code = (string) $xmlElement->code;
                $codesToImport[] = $code;
            }
        }
        
        return $codesToImport;
    }
}
