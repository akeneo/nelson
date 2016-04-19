<?php

namespace Akeneo\System;

use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * This class adapts the Crowdin format to the Akeneo format:
 * - Remove the '---' of all the translations files provided by Crowdin
 * - Change the full format locale (fr_FR) to 2-letters (fr) if needed.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationFilesCleaner
{
    /** @var Executor */
    protected $systemExecutor;

    /** @var string */
    protected $patternSuffix;

    /**
     * @param Executor $systemExecutor
     * @param string   $patternSuffix
     */
    public function __construct(Executor $systemExecutor, $patternSuffix)
    {
        $this->systemExecutor = $systemExecutor;
        $this->patternSuffix  = $patternSuffix;
    }

    /**
     * @param array  $localeMap
     * @param string $cleanerDir
     */
    public function cleanFiles(array $localeMap, $cleanerDir)
    {
        $finder = new Finder();
        $translatedFiles = $finder->in($cleanerDir)->files();

        foreach ($translatedFiles as $file) {
            $this->cleanCrowdinYamlTranslation($file);
            $this->renameTranslation($file, $localeMap);
        }
    }

    /**
     * Crowdin adds "---" on the beginning of every Yaml during the export. This function removes the first line of
     * a file.
     *
     * @param SplFileInfo $file
     */
    protected function cleanCrowdinYamlTranslation(SplFileInfo $file)
    {
        $fileContent = file($file->getRealPath());

        if (count($fileContent)) {
            if (1 === preg_match("/^---/", $fileContent[0])) {
                unset($fileContent[0]);
            }

            file_put_contents($file->getRealPath(), $fileContent);
        }
    }

    /**
     * Returns a 2-letters locale if any map is needed
     *
     * @param string $locale
     * @param array  $localeMap
     *
     * @return null|string
     */
    protected function getSimpleLocale($locale, array $localeMap)
    {
        if (array_key_exists($locale, $localeMap)) {
            return $localeMap[$locale];
        }

        if (preg_match("/^[a-z]{2}_/i", $locale)) {
            return substr($locale, 0, 2);
        }

        return null;
    }

    /**
     * Rename the translation from complete locale format to 2-letters format.
     * For example, rename validators.fr_FR.yml to validators.fr.yml; this will erase the current translation file.
     *
     * @param SplFileInfo $file
     * @param array       $localeMap
     */
    protected function renameTranslation($file, $localeMap)
    {
        $pathInfo = pathinfo($file);

        $locale = pathinfo($pathInfo['filename'], PATHINFO_EXTENSION);
        $simpleLocale = $this->getSimpleLocale($locale, $localeMap);

        if ($simpleLocale) {
            $target = sprintf(
                '%s/%s.%s.%s',
                $pathInfo['dirname'],
                pathinfo($pathInfo['filename'], PATHINFO_FILENAME),
                $simpleLocale,
                $pathInfo['extension']
            );

            rename($file, $target);
        }
    }

    public function moveFiles($cleanerDir, $projectDir)
    {
        $this->systemExecutor->execute(sprintf(
            'cp -r %s%s%s%s* %s%s',
            $cleanerDir,
            DIRECTORY_SEPARATOR,
            $this->patternSuffix,
            DIRECTORY_SEPARATOR,
            $projectDir,
            DIRECTORY_SEPARATOR,
            $cleanerDir
        ));
        $this->systemExecutor->execute(sprintf('rm -rf %s', $cleanerDir));
    }
}
