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
     * If baseBranch is set, returns only folders under this branch.
     *
     * @param string|null $baseBranch
     *
     * @return string[]
     */
    public function getExistingFolders($baseBranch = null)
    {
        $rootNode = $this->getInfo();

        if (null !== $baseBranch) {
            $branchNode = $this->getBranchNode($rootNode, $baseBranch);
            if (null !== $branchNode) {
                $rootNode = $branchNode;
            } else {
                return [];
            }
        }

        return $this->getFolders($rootNode, null);
    }

    /**
     * Returns the list of existing files in Crowdin project.
     * Example: ["PimCommunity/ImportExportBundle/validators.en.yml", "PimCommunity/LocalizationBundle/messages.en.yml"]
     * If baseBranch is set, returns only files under this branch.
     *
     * @param string|null $baseBranch
     *
     * @return string[]
     */
    public function getExistingFiles($baseBranch = null)
    {
        $rootNode = $this->getInfo();

        if (null !== $baseBranch) {
            $branchNode = $this->getBranchNode($rootNode, $baseBranch);
            if (null !== $branchNode) {
                $rootNode = $branchNode;
            } else {
                return [];
            }
        }

        return $this->getFiles($rootNode, null);
    }

    /**
     * @param string $baseBranch
     *
     * @return bool
     */
    public function isBranchCreated($baseBranch)
    {
        return null !== $this->getBranchNode($this->getInfo(), $baseBranch);
    }

    /**
     * @param \SimpleXMLElement $rootNode
     * @param string            $baseBranch
     *
     * @return \SimpleXMLElement|null
     */
    protected function getBranchNode($rootNode, $baseBranch)
    {
        foreach ($rootNode->xpath('files/item') as $item) {
            if ('branch' === (string) $item->node_type && $baseBranch === (string) $item->name) {
                return $item;
            }
        }

        return null;
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
        foreach ($xmlNode->xpath('files/item') as $item) {
            $node_type = (string) $item->node_type;
            if ('directory' === $node_type) {
                $subPath = (string) (null === $path ? $item->name : sprintf("%s/%s", $path, $item->name));
                $result = array_merge($result, $this->getFiles($item, $subPath));
            } elseif ('file' === $node_type) {
                $result[] = sprintf("%s/%s", $path, $item->name);
            }
        }

        return $result;
    }

    /**
     * Load the project information.
     */
    protected function getInfo()
    {
        /** @var Info $service */
        $service = $this->client->api('info');
        $xml = $service->execute();
        return simplexml_load_string($xml);
    }
}
