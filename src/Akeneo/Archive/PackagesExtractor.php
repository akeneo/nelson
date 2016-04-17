<?php

namespace Akeneo\Archive;

/**
 * Class PackagesExtractor
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
