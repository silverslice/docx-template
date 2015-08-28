<?php

namespace Silverslice\DocxTemplate;

class Template
{
    /** @var Zip */
    protected $zip;

    /**
     * @var string Temporary directory for unzipped files
     */
    protected $tempDir;

    /**
     * @var string Contents of document.xml file
     */
    protected $contents;

    public function __construct()
    {
        $this->zip = new Zip();
    }

    /**
     * Opens docx file
     *
     * @param $file
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function open($file)
    {
        if (!is_file($file)) {
            throw new \Exception("File $file not found");
        }

        $dir = $this->getTempDir();
        $this->zip->extract($file, $dir);

        return $this;
    }

    /**
     * Replaces all occurrences of the search string with the replacement string in document
     *
     * @param string $search  The value being searched for. An array may be used to designate multiple needles
     * @param string $replace The replacement value that replaces found search values. An array may be used to designate multiple replacements
     * @return $this
     */
    public function replace($search, $replace)
    {
        $contents = $this->getDocumentContents();
        $this->contents = str_replace($search, $replace, $contents);

        return $this;
    }

    /**
     * Saves file
     *
     * @param $filename
     *
     * @return bool
     */
    public function save($filename)
    {
        if (isset($this->contents)) {
            file_put_contents($this->tempDir . '/word/document.xml', $this->contents);
        }

        $res = $this->zip->archive($this->tempDir, $filename);
        (new FileHelper())->removeDirectory($this->tempDir);

        return $res;
    }

    /**
     * Sets temporary directory
     *
     * @param string $dir
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setTempDirectory($dir)
    {
        if (!is_dir($dir)) {
            throw new \Exception("Directory $dir not found");
        }

        $this->tempDir = $dir;

        return $this;
    }

    protected function getTempDir()
    {
        if (!isset($this->tempDir)) {
            $this->tempDir = sys_get_temp_dir() . '/' . uniqid('docx');
        }

        return $this->tempDir;
    }

    protected function getDocumentContents()
    {
        if (!isset($this->contents)) {
            $this->contents = file_get_contents($this->tempDir . '/word/document.xml');
        }

        return $this->contents;
    }
}