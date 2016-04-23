<?php

namespace Akeneo\System;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->output = new ConsoleOutput();
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
     * @param string $comment
     */
    protected function writeProcessing($comment)
    {
        $this->output->writeln(sprintf('%s <comment>%s<blink>...</blink></comment>', $this->getTime(), $comment));
    }

    /**
     * Write an info message
     *
     * @param $info
     */
    protected function writeInfo($info)
    {
        $this->output->writeln(sprintf('%s   - <comment>%s</comment>', $this->getTime(), $info));
    }

    /**
     * Write a success message
     *
     * @param string $success
     */
    protected function writeSuccess($success)
    {
        $this->output->writeln(sprintf('%s <info>%s</info>', $this->getTime(), $success));
    }
}
