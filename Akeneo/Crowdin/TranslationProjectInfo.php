<?php

namespace Akeneo\Crowdin;

use Crowdin\Api\Info;
use Crowdin\Client;
use Psr\Log\LoggerInterface;

class TranslationProjectInfo
{
    /** @var Client */
    protected $client;

    /** @var LoggerInterface */
    protected $logger;

    /** @var \SimpleXMLElement */
    protected $infos;

    /**
     * @param Client          $client
     * @param LoggerInterface $logger
     */
    public function __construct(Client $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->infos  = null;
    }

    /**
     * Returns the list of existing folders in Crowdin project
     * Example : ["AkeneoCommunity", "AkeneoCommunity/BatchBundle"]
     *
     * @return string[]
     */
    public function getExistingFolders()
    {
        $this->loadInfos();

        return $this->getFolders($this->infos, null);
    }

    /**
     * Returns the list of existing files in Crowdin project.
     * Example: ["PimCommunity/ImportExportBundle/validators.en.yml", "PimCommunity/LocalizationBundle/messages.en.yml"]
     *
     * @return string[]
     */
    public function getExistingFiles()
    {
        $this->loadInfos();

        return $this->getFiles($this->infos, null);
    }

    /**
     * Recursive method to get folders from current path.
     *
     * @param \SimpleXMLElement $xmlNode Current XML node
     * @param string|null       $path    Current folders path
     *
     * @return string[]
     */
    protected function getFolders($xmlNode, $path)
    {
        $result = [];
        foreach ($xmlNode->xpath('files/item') as $item) {
            if ('directory' === (string) $item->node_type) {
                $subPath = (string) (null === $path ? $item->name : sprintf("%s/%s", $path, $item->name));
                $result[] = $subPath;
                $result = array_merge($result, $this->getFolders($item, $subPath));
            }
        }

        return $result;
    }

    /**
     * Recursive method to get files from current path.
     *
     * @param \SimpleXMLElement $xmlNode Current XML node
     * @param string|null       $path    Current folders path
     *
     * @return string[]
     */
    protected function getFiles($xmlNode, $path)
    {
        $result = [];
        foreach ($xmlNode->xpath('files/item') as $dir) {
            $node_type = (string) $dir->node_type;
            if ('directory' === $node_type) {
                $subPath = (string) (null === $path ? $dir->name : sprintf("%s/%s", $path, $dir->name));
                $result = array_merge($result, $this->getFiles($dir, $subPath));
            } elseif ('file' === $node_type) {
                $result[] = sprintf("%s/%s", $path, $dir->name);
            }
        }

        return $result;
    }

    /**
     * Load the project information.
     */
    protected function loadInfos()
    {
        if (null === $this->infos) {
            /** @var Info $service */
            $service = $this->client->api('info');
            $xml = $service->execute();
            $this->infos = simplexml_load_string($xml);
        }
    }
}
