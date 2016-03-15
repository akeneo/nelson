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
     * Transforms
     *    <projectDir>/src/Pim/Bundle/UserBundle/Resources/translations/messages.en.yml
     *    <projectDir>/src/PimEnterprise/Bundle/BaseConnectorBundle/Resources/translations/messages.en.yml
     *    <projectDir>/src/Akeneo/Bundle/BatchBundle/Resources/translations/validators.en.yml
     * into
     *    PimCommunity/UserBundle/messages.en.yml
     *    PimEnterprise/BaseConnectorBundle/messages.en.yml
     *    AkeneoCommunity/BatchBundle/validators.en.yml
     *
     * @return string
     */
    public function getTarget()
    {
        $search = [
            'src/Pim/Bundle',
            'src/PimEnterprise/Bundle',
            $this->projectDir . '/',
            '/Resources/translations',
        ];
        $replace = [
            'PimCommunity',
            'PimEnterprise',
            '',
            '',
        ];
        if (preg_match(sprintf('/%s$/i', $this->edition), $this->projectDir)) {
            $search[]  = 'src/Akeneo/Bundle';
            $replace[] = sprintf('Akeneo%s', ucfirst($this->edition));
        }
        $targetPath = str_replace($search, $replace, $this->source);

        return $targetPath;
    }

    /**
     * Returns the Crowdin directory of the translation file.
     *
     * @return string
     */
    public function getTargetDirectory()
    {
        return dirname($this->getTarget());
    }

    /**
     * Returns the pattern of the file when you download the Crowdin archive.
     *
     * @return string
     */
    public function getPattern()
    {
        $targetPath = str_replace([$this->projectDir], [ucfirst($this->edition)], $this->source);
        $dir = dirname($targetPath);

        return $dir . '/%file_name%.%locale_with_underscore%.%file_extension%';
    }
}
