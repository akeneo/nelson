<?php

namespace Akeneo\System;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;

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

    /** @var Translator */
    protected $translator;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->output = new ConsoleOutput();
        $this->configureTranslator();

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
        array_unshift($messageParams, $this->addBold($this->translator->trans($message)));
        $this->output->writeln(sprintf(
            '%s <comment>%s<blink>...</blink></comment>',
            $this->getTime(),
            call_user_func_array('sprintf', $messageParams)
        ));
    }

    /**
     * Write an info message
     *
     * @param string   $message
     * @param string[] $messageParams
     */
    protected function writeInfo($message, $messageParams = [])
    {
        array_unshift($messageParams, $this->addBold($this->translator->trans($message)));
        $this->output->writeln(sprintf(
            '%s   - <comment>%s</comment>',
            $this->getTime(),
            call_user_func_array('sprintf', $args)
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
        array_unshift($messageParams, $this->addBold($this->translator->trans($message)));
        $this->output->writeln(sprintf(
            '%s <info>%s</info>',
            $this->getTime(),
            call_user_func_array('sprintf', $args)
        ));
    }

    /**
     * Add bold format for arguments
     *
     * @param string $message
     *
     * @return string
     */
    protected function addBold($message)
    {
        return str_replace('%s', '<bold>%s</bold>', $message);
    }

    /**
     * Configure Translator.
     *
     * TODO Put it elsewhere
     */
    protected function configureTranslator()
    {
        $this->translator = new Translator('fr_FR', new MessageSelector());
        $this->translator->addLoader('yaml', new YamlFileLoader());
        $finder = new Finder();
        $finder->files()->in(dirname(__FILE__) . '/../Resources/translations/')->name('*.yml');
        foreach ($finder->getIterator() as $file) {
            $this->translator->addResource('yaml', $file->getPathName(), basename($file->getFileName(), '.yml'));
        }
    }
}
