<?php

namespace Silverslice\DocxTemplate;

class Zip
{
    /**
     * Extracts files from archive
     *
     * @param string $file
     * @param string $where
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function extract($file, $where)
    {
        $zip = new \ZipArchive();
        $res = $zip->open($file);

        if ($res === false) {
            throw new \Exception('Unable to extract file(s). Error code: ' . $res);
        }

        $zip->extractTo($where);
        $zip->close();

        return true;
    }

    /**
     * Adds file or directory to archive
     *
     * @param string $source
     * @param string $destination
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function archive($source, $destination)
    {
        if (!file_exists($source)) {
            throw new \Exception('File or directory not found');
        }

        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZIPARCHIVE::OVERWRITE)) {
            throw new \Exception('Unable to open zip archive');
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                    continue;
                }

                $file = realpath($file);
                $file = str_replace('\\', '/', $file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else {
                    if (is_file($file) === true) {
                        $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file, FILE_BINARY));
                    }
                }
            }
        } else {
            if (is_file($source) === true) {
                $zip->addFromString(basename($source), file_get_contents($source, FILE_BINARY));
            }
        }

        return $zip->close();
    }
}