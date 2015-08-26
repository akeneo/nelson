<?php

namespace Akeneo\Archive;

/**
 * Class PackagesExtractor
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PackagesExtractor
{
    /**
     * @param array  $packages
     * @param string $downloadDir
     * @param string $extractDir
     */
    public function extract(array $packages, $downloadDir, $extractDir)
    {
        $zip = new \ZipArchive();
        foreach ($packages as $package) {
            $path = sprintf('%s/%s.zip', $downloadDir, $package);
            $zip->open($path);
            $zip->extractTo($extractDir);
        }
        $zip->close();
    }
}