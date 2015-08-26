<?php


namespace Akeneo\Archive;

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

            if (array_key_exists($locale, $localeMap)) {
                $target = sprintf(
                    '%s/%s.%s.%s',
                    $pathinfo['dirname'],
                    pathinfo($pathinfo['filename'], PATHINFO_FILENAME),
                    $localeMap[$locale],
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
        $array = file($file->getRealPath());

        if (count($array)) {
            // Crowdin adds --- on the beginning of every Yaml during the export.
            if (1 === preg_match("/^---/", $array[0])) {
                unset($array[0]);
            }

            file_put_contents($file->getRealPath(), $array);
        }
    }
}