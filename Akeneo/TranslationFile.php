<?php

namespace Akeneo;

/**
 * Contains information about the file to translate.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class TranslationFile
{
    /** @var string */
    protected $source;

    /** @var string */
    protected $projectDir;

    /** @var string */
    protected $edition;

    /**
     * @param string $source
     * @param string $projectDir
     * @param string $edition
     */
    public function __construct($source, $projectDir, $edition)
    {
        $this->source     = $source;
        $this->projectDir = $projectDir;
        $this->edition    = $edition;
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
     * Returns the relative path of the file to translate for Crowdin.
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->getTargetCrowdinPath($this->source, $this->projectDir, $this->edition);
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
