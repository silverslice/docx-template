<?php

namespace Silverslice\DocxTemplate;

class FileHelper
{
    /**
     * Removes directory recursively
     *
     * @param string $dir
     * @return bool
     */
    public function removeDirectory($dir)
    {
        foreach (new \RecursiveIteratorIterator(
                     new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
                     \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
        }

        return rmdir($dir);
    }
}