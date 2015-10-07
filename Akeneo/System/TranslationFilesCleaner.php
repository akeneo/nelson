<?php


namespace Akeneo\System;

/**
 * Class TranslationFilesCleaner
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
            $pathinfo = pathinfo($file);
            if (!isset($pathinfo['extension']) || $pathinfo['extension'] !== 'yml') {
                continue;
            }

            $dirname = $pathinfo['dirname'];
            $pattern = '/translations$/';
            $isATranslationFile = preg_match($pattern, $dirname);
            if ($isATranslationFile !== 1) {
                continue;
            }

            $this->cleanCrowdinYamlTranslation($file);

            $locale = pathinfo($pathinfo['filename'], PATHINFO_EXTENSION);

            $originalFile = sprintf(
                '%s/%s.%s.%s',
                $pathinfo['dirname'],
                pathinfo($pathinfo['filename'], PATHINFO_FILENAME),
                'en',
                $pathinfo['extension']
            );

            if (!file_exists($originalFile)) {
                unlink($file);
                continue;
            }

            $simpleLocale = $this->getSimpleLocale($locale, $localeMap);
            if ($simpleLocale) {
                $target = sprintf(
                    '%s/%s.%s.%s',
                    $pathinfo['dirname'],
                    pathinfo($pathinfo['filename'], PATHINFO_FILENAME),
                    $simpleLocale,
                    $pathinfo['extension']
                );

                rename($file, $target);
            }
        }
    }

    /**
     * @param \SplFileInfo $file
     */
    protected function cleanCrowdinYamlTranslation(\SplFileInfo $file)
    {
        $fileContent = file($file->getRealPath());

        if (count($fileContent)) {
            // Crowdin adds --- on the beginning of every Yaml during the export.
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
}
