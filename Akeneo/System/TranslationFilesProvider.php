<?php

namespace Akeneo\System;

use Symfony\Component\Finder\Finder;

/**
 * Class TranslationFilesProvider
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class TranslationFilesProvider
{
    /**
     * @param $projectDir
     *
     * @return array
     */
    public function provideTranslations($projectDir)
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
            $files[] = [
                'source' => $translationFile->getRealPath(),
                'target' => $this->getTargetCrowdinPath($translationFile->getRealPath(), $projectDir)
            ];
        }

        return $files;
    }

    /**
     * Transforms
     *    <projectDir>/src/Pim/Bundle/UserBundle/Resources/translations/messages.en.yml
     *    <projectDir>/src/PimEnterprise/Bundle/BaseConnectorBundle/Resources/translations/messages.en.yml
     * into
     *    PimCommunity/UserBundle/messages.en.yml
     *    PimEnterprise/BaseConnectorBundle/messages.en.yml
     */
    protected function getTargetCrowdinPath($filePath, $projectDir)
    {
        $targetPath = str_replace(
            [
                'src/Pim/Bundle',
                'src/PimEnterprise/Bundle',
                $projectDir . '/',
                '/Resources/translations'
            ],
            [
                'PimCommunity',
                'PimEnterprise'
            ],
            $filePath
        );

        return $targetPath;
    }
}
