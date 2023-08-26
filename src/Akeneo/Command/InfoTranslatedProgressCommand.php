<?php

namespace Akeneo\Command;

use Akeneo\Crowdin\TranslatedProgressSelector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Display the packages for next pull request
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InfoTranslatedProgressCommand extends Command
{
    /** @var TranslatedProgressSelector */
    private $translatedProgressSelector;

    public function __construct(TranslatedProgressSelector $translatedProgressSelector)
    {
        parent::__construct();
        $this->translatedProgressSelector = $translatedProgressSelector;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('nelson:info-translated-progress')
            ->setDescription('Displays the languages that will be merged');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->translatedProgressSelector->display($output);

        return Command::SUCCESS;
    }
}
