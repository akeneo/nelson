<?php

namespace Akeneo\Nelson;

/**
 * Contains information about the file to translate.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationFile
{
    /** @var string */
    protected $source;

    /** @var string */
    protected $projectDir;

    /** @var string */
    protected $patternSuffix;

    /**
     * @param string $source
     * @param string $projectDir
     * @param string $patternSuffix
     */
    public function __construct($source, $projectDir, $patternSuffix)
    {
        $this->source        = $source;
        $this->projectDir    = $projectDir;
        $this->patternSuffix = $patternSuffix;
    }

    /**
     * Returns the absolute path of the file to translate.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Returns the project dir.
     *
     * @return string
     */
    public function getProjectDir()
    {
        return $this->projectDir;
    }

    /**
     * Returns the pattern of the file when you download the Crowdin archive.
     *
     * @return string
     */
    public function getPattern()
    {
        $targetPath = str_replace([$this->projectDir], '', $this->source);
        if (null !== $this->patternSuffix && '' !== $this->patternSuffix) {
            $targetPath = sprintf('%s%s%s', $this->patternSuffix, DIRECTORY_SEPARATOR, $targetPath);
        }
        $dirName = dirname($targetPath);
        $baseName = basename($targetPath);
        $filename = '%file_name%';

        $matches = null;
        // If filename looks like 'translations.en.yml', it generates a pattern to remove the 'en' part.
        if (preg_match('/^(?P<filename>\w+)\.[A-Za-z]{2}.\w+$/', $baseName, $matches)) {
            $filename = $matches['filename'];
        }

        return '/'. $dirName . '/' . $filename . '.%locale_with_underscore%.%file_extension%';
    }
}
