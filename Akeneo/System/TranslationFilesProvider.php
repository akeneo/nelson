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
     * @param string $projectDir
     * @param string $edition    'community'|'enterprise'
     *
     * @return array
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
            $files[] = [
                'source' => $translationFile->getRealPath(),
                'target' => $this->getTargetCrowdinPath($translationFile->getRealPath(), $projectDir, $edition)
            ];
        }

        return $files;
    }

    /**
     * Transforms
     *    <projectDir>/src/Pim/Bundle/UserBundle/Resources/translations/messages.en.yml
     *    <projectDir>/src/PimEnterprise/Bundle/BaseConnectorBundle/Resources/translations/messages.en.yml
     *    <projectDir>/src/Akeneo/Bundle/BatchBundle/Resources/translations/validators.en.yml
     * into
     *    PimCommunity/UserBundle/messages.en.yml
     *    PimEnterprise/BaseConnectorBundle/messages.en.yml
     *    AkeneoCommunity/BatchBundle/validators.en.yml
     *
     * @param string $filePath   Absolute file path of the translation file
     * @param string $projectDir Absolute file directory of the project
     * @param string $edition    'community'|'enterprise'
     *
     * @return string
     */
    protected function getTargetCrowdinPath($filePath, $projectDir, $edition)
    {
        $search = [
            'src/Pim/Bundle',
            'src/PimEnterprise/Bundle',
            $projectDir . '/',
            '/Resources/translations',
        ];
        $replace = [
            'PimCommunity',
            'PimEnterprise',
            '',
            '',
        ];
        if (preg_match(sprintf('/%s$/i', $edition), $projectDir)) {
            $search[]  = 'src/Akeneo/Bundle';
            $replace[] = sprintf('Akeneo%s', ucfirst($edition));
        }
        $targetPath = str_replace($search, $replace, $filePath);

        return $targetPath;
    }
}
