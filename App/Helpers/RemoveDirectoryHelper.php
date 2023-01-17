<?php

declare(strict_types=1);

namespace Phphleb\sitemapgen\App\Helpers;

class RemoveDirectoryHelper
{
    public function removeFiles(string $directory): bool
    {
        if (!is_dir($directory)) {
            return false;
        }
        $includes = new \FilesystemIterator($directory);
        foreach ($includes as $include) {
            if(is_dir((string)$include) && !is_link((string)$include)) {
                recursiveRemoveDir($include);
            }
        }
        return true;
    }

    public function removeFilesAndDirectory(string $directory): bool
    {
        if ($this->removeFiles()) {
            rmdir($directory);
            return true;
        }
        return false;
    }

    public function revertGeneratedFile() {
        $dir = HLEB_PUBLIC_DIR . DIRECTORY_SEPARATOR . 'sitemapgen' . DIRECTORY_SEPARATOR;
        file_put_contents($dir . 'sitemap-generated.xml', file($dir . 'sitemap-default.xml'));
    }
}