<?php

namespace Akeneo\System;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AbstractConsoleLogger implements EventSubscriberInterface
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
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [];
    }

    /**
     * @return string
     */
    protected function getTime()
    {
        return date('[H:i:s]');
    }

    /**
     * @param string $comment
     */
    protected function writeComment($comment)
    {
        $this->output->writeln(sprintf('%s <comment>%s<blink>...</blink></comment>', $this->getTime(), $comment));
    }

    /**
     * @param string $success
     */
    protected function writeSuccess($success)
    {
        $this->output->writeln(sprintf('%s <info>%s</info>', $this->getTime(), $success));
    }
}
