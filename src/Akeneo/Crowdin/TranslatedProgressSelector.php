<?php

namespace Akeneo\Crowdin;

use Akeneo\Event\Events;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

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

    /** @var null|array */
    protected $folders;

    /** @var array */
    protected $branches;

    /**
     * @param Client                   $client
     * @param EventDispatcherInterface $eventDispatcher
     * @param int                      $minTranslatedProgress
     * @param null|array               $folders
     * @param array                    $branches
     */
    public function __construct(
        Client $client,
        EventDispatcherInterface $eventDispatcher,
        $minTranslatedProgress = 0,
        $folders = null,
        $branches = ['master']
    ) {
        $this->client = $client;
        $this->eventDispatcher = $eventDispatcher;
        $this->minTranslatedProgress = $minTranslatedProgress;
        $this->folders = $folders;
        $this->branches = $branches;
    }

    /**
     * Display the packages to import
     *
     * @param OutputInterface $output
     */
    public function display(OutputInterface $output)
    {
        foreach ($this->branches as $branch) {
            $output->write(
                sprintf("Languages exported for %s branch (%d%%):", $branch, $this->minTranslatedProgress),
                true
            );
            $table = new Table($output);
            $table->setHeaders(['locale', 'percentage']);
            foreach ($this->packages(true, $branch) as $code => $progress) {
                $table->addRow([$code, sprintf('%d%%', $progress)]);
            }
            $table->render();
        }
    }

    /**
     * Return the list of packages to import
     *
     * @param bool        $exclude If set to true, exclude the packages with a translation level too low
     * @param string|null $branch  If set, list the package for a specific branch
     *
     * @return array
     */
    public function packages($exclude = true, $branch = null)
    {
        $this->eventDispatcher->dispatch(new Event(), Events::PRE_CROWDIN_PACKAGES);

        $maxApproved = -1;
        $approvedCounts = [];
        foreach ($this->getAllCrowdinCodes() as $code) {
            $approved = $this->getApprovedCount($code, $branch);
            $approvedCounts[$code] = $approved;
            $maxApproved = max($maxApproved, $approved);
        }

        $result = [];
        $minApprovedCount = $maxApproved * $this->minTranslatedProgress / 100;
        foreach ($approvedCounts as $code => $approvedCount) {
            if (!$exclude || $approvedCount > $minApprovedCount) {
                $result[$code] = $approvedCount / $maxApproved * 100;
            }
        }

        asort($result);
        $result = array_reverse($result);

        $this->eventDispatcher->dispatch(new GenericEvent($this, [
            'count' => count($result),
        ]), Events::POST_CROWDIN_PACKAGES);

        return $result;
    }

    /**
     * Returns the count of approved strings for a specific language code.
     *
     * @param string      $crowdinCode
     * @param null|string $branch
     *
     * @return int
     */
    protected function getApprovedCount($crowdinCode, $branch = null)
    {
        $query = $this->client->api('language-status');
        $query->setLanguage($crowdinCode);
        $response = $query->execute();

        $xml = simplexml_load_string($response);

        $approved = 0;
        foreach ($xml->files->item as $mainNode) {
            if ((null !== $branch) && ('branch' === (string) $mainNode->node_type) && ($branch === (string) $mainNode->name)) {
                foreach ($mainNode->files->item as $mainDir) {
                    if (null === $this->folders || [null] === $this->folders || in_array(
                        (string) $mainDir->name,
                        $this->folders
                    )) {
                        $approved += (int) $mainDir->approved;
                    }
                }
            }
        }

        return $approved;
    }

    /**
     * Returns all the Crowdin codes of the languages for this project.
     *
     * @return string[]
     */
    protected function getAllCrowdinCodes()
    {
        $response = $this->client->api('status')->execute();
        $xml = simplexml_load_string($response);
        $codes = [];
        foreach ($xml as $xmlElement) {
            $codes[] = (string) $xmlElement->code;
        }

        return $codes;
    }
}
