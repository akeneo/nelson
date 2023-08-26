<?php

namespace Akeneo\System;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator as BaseTranslator;

/**
 * Translates Nelson messages
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Translator extends BaseTranslator
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        string $locale,
        ?MessageFormatterInterface $messageFormatter = null,
        ?string $cacheDir = null,
        ?bool $debug = false
    ) {
        parent::__construct($locale, $messageFormatter, $cacheDir, $debug);

        $this->addLoader('yaml', new YamlFileLoader());
        $finder = new Finder();
        $finder->files()->in(dirname(__FILE__) . '/../Resources/translations/')->name('*.yml');

        foreach ($finder->getIterator() as $file) {
            $this->addResource('yaml', $file->getPathName(), basename($file->getFileName(), '.yml'));
        }
    }
}
