<?php

namespace Akeneo\Crowdin;

use Akeneo\Event\Events;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

/**
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

    public function __construct(
        Client $client,
        EventDispatcherInterface $eventDispatcher,
        int $minTranslatedProgress = 0,
        ?array $folders = null,
        ?array $branches = ['master']
    ) {
        $this->client = $client;
        $this->eventDispatcher = $eventDispatcher;
        $this->minTranslatedProgress = $minTranslatedProgress;
        $this->folders = $folders;
        $this->branches = $branches;
    }

    /**
     * Display the packages to import
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
     */
    public function packages(?bool $exclude = true, ?string $branch = null): array
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

        $this->eventDispatcher->dispatch(
            new GenericEvent($this, [
                'count' => count($result),
            ]),
            Events::POST_CROWDIN_PACKAGES
        );

        return $result;
    }

    /**
     * Returns the count of approved strings for a specific language code.
     */
    protected function getApprovedCount(string $crowdinCode, ?string $branch = null): int
    {
        $query = $this->client->api('language-status');
        $query->setLanguage($crowdinCode);
        $response = $query->execute();

        $xml = simplexml_load_string($response);

        $approved = 0;
        foreach ($xml->files->item as $mainNode) {
            if ((null !== $branch) && ('branch' === (string) $mainNode->node_type) && ($branch === (string) $mainNode->name)) {
                foreach ($mainNode->files->item as $mainDir) {
                    if (null === $this->folders || [null] === $this->folders || in_array((string) $mainDir->name, $this->folders)) {
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
    protected function getAllCrowdinCodes(): array
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
