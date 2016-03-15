<?php

namespace Akeneo\System;

use SplFileInfo;

/**
 * This class adapts the Crowdin format to the Akeneo format:
 * - Remove the '---' of all the translations files provided by Crowdin
 * - Change the full format locale (fr_FR) to 2-letters (fr) if needed.
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class TranslationFilesCleaner
{
    /**
     * @param array  $localeMap
     * @param string $projectDir
     */
    public function cleanFiles(array $localeMap, $projectDir)
    {
        $dirIterator = new \RecursiveDirectoryIterator($projectDir);
        $iterator    = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $file) {
            if ($this->isTranslation($file)) {
                if (!$this->existsOriginalTranslation($file)) {
                    unlink($file);
                } else {
                    $this->cleanCrowdinYamlTranslation($file);
                    $this->renameTranslation($file, $localeMap);
                }
            }
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
     * Check if the current file is a translation, i.e. is a YML and belongs to a "translations" folder.
     *
     * @param SplFileInfo $file
     *
     * @return bool
     */
    protected function isTranslation($file)
    {
        $pathInfo = pathinfo($file);
        if (!isset($pathInfo['extension']) || $pathInfo['extension'] !== 'yml') {
            return false;
        }

        $dirName = $pathInfo['dirname'];
        $pattern = '/translations$/';
        $isATranslationFile = preg_match($pattern, $dirName);
        return ($isATranslationFile === 1);
    }

    /**
     * Check if the original translation is already here. Prevent of removed translation files.
     *
     * @param SplFileInfo $file
     *
     * @return bool
     */
    protected function existsOriginalTranslation($file)
    {
        $pathInfo = pathinfo($file);

        $originalFile = sprintf(
            '%s/%s.%s.%s',
            $pathInfo['dirname'],
            pathinfo($pathInfo['filename'], PATHINFO_FILENAME),
            'en',
            $pathInfo['extension']
        );

        return file_exists($originalFile);
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
}
