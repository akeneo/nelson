<?php

namespace Akeneo\Crowdin;

use Akeneo\Event\Events;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslatedProgressSelectorV2
{
    /** @var NelsonClient */
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
        NelsonClient $client,
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
            $output->writeln(
                sprintf(
                    "Languages exported for %s branch (greater than %d%%)",
                    $branch,
                    $this->minTranslatedProgress
                ),
                true
            );

            $percentages = $this->packages(true, $branch);
            $output->writeln(sprintf("%d results:", count($percentages)));

            $table = new Table($output);
            $table->setHeaders(['locale', 'percentage']);
            foreach ($percentages as $code => $progress) {
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
        $this->eventDispatcher->dispatch(Events::PRE_CROWDIN_PACKAGES);

        $approvedCounts = [];

        $languages = $this->client->translationProgress($branch);

        foreach ($languages as $language) {
            $approvedPercentage = $language->getPhrases()['approved'] / $language->getPhrases()['total'];
            if (!$exclude || $approvedPercentage > $this->minTranslatedProgress) {
                $approvedCounts[$language->getLanguageId()] = 100 * $approvedPercentage;
            }
        }

        $this->eventDispatcher->dispatch(
            Events::POST_CROWDIN_PACKAGES,
            new GenericEvent($this, [
                'count' => count($approvedCounts),
            ])
        );

        return $approvedCounts;
    }
}
