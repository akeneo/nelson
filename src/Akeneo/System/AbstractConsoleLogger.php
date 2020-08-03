<?php

namespace Akeneo\System;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class is an event subscriber and contains methods to display messages on console.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractConsoleLogger implements EventSubscriberInterface
{
    /** @var ConsoleOutputInterface */
    protected $output;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * {@inheritdoc}
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->output     = new ConsoleOutput();

        $formatter = $this->output->getFormatter();
        $formatter->setStyle('blink', new OutputFormatterStyle(null, null, array('blink')));
        $formatter->setStyle('bold', new OutputFormatterStyle(null, null, array('bold')));
    }

    /**
     * Return the time to suffix console messages
     *
     * @return string
     */
    protected function getTime()
    {
        return date('[H:i:s]');
    }

    /**
     * Write a processing message
     *
     * @param string   $message
     * @param string[] $messageParams
     */
    protected function writeProcessing($message, $messageParams = [])
    {
        $this->output->writeln(sprintf(
            '%s <comment>%s<blink>...</blink></comment>',
            $this->getPrefix($messageParams),
            $this->translator->trans($message, $this->prepareTranslationParams($messageParams))
        ));
    }

    protected function writeInfo($message, $messageParams = []): void
    {
        $this->output->writeln(sprintf(
            '%s   - <comment>%s</comment>',
            $this->getPrefix($messageParams),
            $this->translator->trans($message, $this->prepareTranslationParams($messageParams))
        ));
    }

    /**
     * Write a success message
     *
     * @param string   $message
     * @param string[] $messageParams
     */
    protected function writeSuccess($message, $messageParams = [])
    {
        $this->output->writeln(sprintf(
            '%s <info>%s</info>',
            $this->getPrefix($messageParams),
            $this->translator->trans($message, $this->prepareTranslationParams($messageParams))
        ));
    }

    /**
     * Surround translation keys with '%' and bold values
     *
     * @param $params
     *
     * @return array
     */
    protected function prepareTranslationParams($params)
    {
        $result = [];
        foreach ($params as $key => $value) {
            $result['%' . $key . '%'] = '<bold>' . $value . '</bold>';
        }

        return $result;
    }

    /**
     * Get formatted string of 'dry-run' info.
     *
     * @param boolean $dryRun
     *
     * @return string
     */
    protected function formatDryRun($dryRun)
    {
        return $dryRun ? '<info>[dry-run]</info> ' : '';
    }

    /**
     * @param array $messageParams
     *
     * @return string
     */
    private function getPrefix($messageParams)
    {
        return sprintf(
            '%s%s',
            $this->getTime(),
            $this->formatDryRun(isset($messageParams['dry_run']) && $messageParams['dry_run'])
        );
    }
}
