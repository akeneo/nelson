<?php

namespace Akeneo\Nelson;

/**
 * Resolves the Crowdin target from the source path
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TargetResolver
{
    /** @var array */
    protected $targetRules;

    /**
     * @param array $targetRules
     */
    public function __construct(array $targetRules)
    {
        $this->targetRules = $targetRules;
    }

    /**
     * @param string $projectDir
     * @param string $source
     *
     * @return string
     */
    public function getTarget($projectDir, $source)
    {
        $searches = [$projectDir . '/'];
        $replaces = [''];

        foreach ($this->targetRules as $search => $replace) {
            $searches[] = $search;
            $replaces[] = $replace;
        }
        $targetPath = str_replace($searches, $replaces, $source);

        return $targetPath;
    }

    /**
     * Returns the Crowdin directory of the translation file.
     *
     * @param string $projectDir
     * @param string $source
     *
     * @return string
     */
    public function getTargetDirectory($projectDir, $source)
    {
        return dirname($this->getTarget($projectDir, $source));
    }
}
