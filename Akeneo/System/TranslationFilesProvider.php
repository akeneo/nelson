<?php

namespace Akeneo\System;

use Akeneo\TranslationFile;
use Symfony\Component\Finder\Finder;

/**
 * Class TranslationFilesProvider
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class TranslationFilesProvider
{
    /**
     * @param string $projectDir
     * @param string $edition    'community'|'enterprise'
     *
     * @return TranslationFile[]
     */
    public function provideTranslations($projectDir, $edition)
    {
        $finder = new Finder();

        $translationFiles = $finder
            ->in($projectDir . '/src/')
            ->notPath('/Oro/')
            ->path('/Resources\/translations/')
            ->name('*.en.yml')
            ->files();

        $files = [];
        foreach ($translationFiles as $translationFile) {
            $files[] = new TranslationFile($translationFile->getRealPath(), $projectDir, $edition);
        }

        return $files;
    }
}
