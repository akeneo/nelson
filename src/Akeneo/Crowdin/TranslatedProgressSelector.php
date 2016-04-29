<?php

namespace Akeneo\Crowdin;

use Akeneo\Event\Events;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class TranslatedProgressSelector
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslatedProgressSelector
{
    /** @var Client */
    protected $client;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var int */
    protected $minTranslatedProgress;

    /**
     * @param Client $client
     * @param int    $minTranslatedProgress
     */
    public function __construct(Client $client, EventDispatcherInterface $eventDispatcher, $minTranslatedProgress = 0)
    {
        $this->client                = $client;
        $this->eventDispatcher       = $eventDispatcher;
        $this->minTranslatedProgress = $minTranslatedProgress;
    }

    /**
     * Display the packages to import
     *
     * @param OutputInterface $output
     */
    public function display(OutputInterface $output)
    {
        foreach ($this->packages() as $code => $progress) {
            $output->write(sprintf('%s (%s%%)', $code, $progress), true);
        }
    }

    /**
     * Return the list of packages to import
     *
     * @return array
     */
    public function packages()
    {
        $this->eventDispatcher->dispatch(Events::PRE_CROWDIN_PACKAGES);

        $response = $this->client->api('status')->execute();
        $xml = simplexml_load_string($response);
        $codesToImport = [];

        foreach ($xml as $xmlElement) {
            $translated_progress = (int) $xmlElement->translated_progress;
            if ($translated_progress >= $this->minTranslatedProgress) {
                $code = (string) $xmlElement->code;
                $codesToImport[$code] = $translated_progress;
            }
        }

        $this->eventDispatcher->dispatch(Events::POST_CROWDIN_PACKAGES, new GenericEvent($this, [
            'count' => count($codesToImport),
        ]));

        return $codesToImport;
    }
}
