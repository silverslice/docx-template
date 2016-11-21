<?php

namespace Silverslice\DocxTemplate;

class Template
{
    /** @var \ZipArchive */
    protected $zip;

    /**
     * @var string Temporary directory for docx file
     */
    protected $tempDir;

    /**
     * @var string Temporary docx file
     */
    protected $tempFilename;

    /**
     * @var string Contents of document.xml file
     */
    protected $contents;

    public function __construct($tempDir = null)
    {
        if ($tempDir && is_dir($tempDir)) {
            $this->tempDir = $tempDir;
        }

        $this->zip = new \ZipArchive();
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

        $this->tempFilename = tempnam($this->getTempDir(), 'docx');
        if (!copy($file, $this->tempFilename)) {
            throw new \Exception("Cannot copy file to temporary directory");
        }

        $res = $this->zip->open($this->tempFilename);
        if ($res !== true) {
            unlink($this->tempFilename);
            throw new \Exception("Unable to unpack docx file");
        }

        return $this;
    }

    /**
     * Replaces all occurrences of the search variable with the replacement string in document
     *
     * @param string $var     The variable being searched for
     * @param string $replace The replacement value that replaces found search variables
     * @return $this
     */
    public function replace($var, $replace)
    {
        $var = '{' . $var . '}';

        $contents = $this->getDocumentContents();
        $this->contents = str_replace($var, $replace, $contents);

        return $this;
    }

    /**
     * Saves file
     *
     * @param $filename
     *
     * @throws \Exception
     */
    public function save($filename)
    {
        if (isset($this->contents)) {
            $this->zip->addFromString('word/document.xml', $this->contents);
        }

        $this->zip->close();

        $res = @rename($this->tempFilename, $filename);
        if (!$res) {
            throw new \Exception("Unable to save file");
        }
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
            $this->tempDir = sys_get_temp_dir();
        }

        return $this->tempDir;
    }

    protected function getDocumentContents()
    {
        if (!isset($this->contents)) {
            $this->contents = $this->joinVariables($this->zip->getFromName('word/document.xml'));
        }

        return $this->contents;
    }

    /**
     * Join variables split by Microsoft Word into several tags
     *
     * @param $contents
     * @return mixed
     */
    protected function joinVariables($contents)
    {
        $contents = preg_replace_callback(
            '#\{([^\}]+)\}#U',
            function ($match) {
                return strip_tags($match[0]);
            },
            $contents
        );

        return $contents;
    }
}