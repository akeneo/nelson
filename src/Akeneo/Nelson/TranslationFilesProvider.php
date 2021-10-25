<?php

namespace Akeneo\Nelson;

use Symfony\Component\Finder\Finder;

/**
 * Class TranslationFilesProvider
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationFilesProvider
{
    /** @var array */
    protected $finderOptions;

    /** @var string|null */
    protected $patternSuffix;

    /**
     * @param array       $finderOptions
     * @param string|null $patternSuffix
     */
    public function __construct(array $finderOptions, $patternSuffix = null)
    {
        $this->finderOptions = $finderOptions;
        $this->patternSuffix = $patternSuffix;
    }

    /**
     * @param string $projectDir
     *
     * @return TranslationFile[]
     */
    public function provideTranslations($projectDir)
    {
        $finder = new Finder();
        if (isset($this->finderOptions['in'])) {
            if (is_array(isset($this->finderOptions['in']))) {
                $inFolders = isset($this->finderOptions['in']);
            } else {
                $inFolders = [isset($this->finderOptions['in'])];
            }
            foreach ($inFolders as $inFolder) {
                $inFolder = sprintf('%s%s%s', $projectDir, DIRECTORY_SEPARATOR, $inFolder);
                $finder->in($inFolder);
            }
        } else {
            $finder->in($projectDir);
        }

        foreach ($this->finderOptions as $function => $argument) {
            if ('in' !== $function) {
                $finder = $finder->$function($argument);
            }
        }
        $translationFiles = $finder->files();

        $files = [];
        foreach ($translationFiles as $translationFile) {
            $files[] = new TranslationFile($translationFile->getRealPath(), $projectDir, $this->patternSuffix);
        }

        return $files;
    }
}
