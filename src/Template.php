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

    /**
     * @var string Footer of footer1.xml file
     */
    protected $footer;

    public function __construct()
    {
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
     * @param bool $escape    Escape special xml symbols
     * @return $this
     */
    public function replace($var, $replace, $escape = true)
    {
        $var = '{' . $var . '}';
        if (is_null($replace)) {
            $replace = '';
        }

        if ($escape) {
            $replace = htmlspecialchars($replace, ENT_QUOTES);
        }

        //Contents
        $contents = $this->getDocumentContents();
        $this->contents = str_replace($var, $replace, $contents);
        //Footer
        $footers = $this->getFooterContents();
        $this->footer = str_replace($var, $replace, $footers);

        return $this;
    }

    /**
     * Replaces all occurrences of the search variable with the replacement multiline string in document
     *
     * @param string $var     The variable being searched for
     * @param string $replace The replacement value that replaces found search variables
     * @param bool $escape    Escape special xml symbols
     * @return $this
     */
    public function replaceMultiline($var, $replace, $escape = true)
    {
        if (is_null($replace)) {
            $replace = '';
        }

        $replace = $this->prepareMultilineString($replace, $escape);

        return $this->replace($var, $replace, false);
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

        if (isset($this->footer)) {
            $this->zip->addFromString('word/footer1.xml', $this->footer);
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

    protected function getFooterContents()
    {
        if (!isset($this->footer)) {
            $this->footer = $this->joinVariables($this->zip->getFromName('word/footer1.xml'));
        }

        return $this->footer;
    }

    /**
     * Join variables split by Microsoft Word into several tags
     *
     * @param $contents
     * @return string
     */
    protected function joinVariables($contents)
    {
        return preg_replace_callback(
            '#\{([^\}]+)\}#U',
            function ($match) {
                return strip_tags($match[0]);
            },
            $contents
        );
    }

    protected function prepareMultilineString($string, $escape)
    {
        if ($escape) {
            $string = htmlspecialchars($string, ENT_QUOTES);
        }
        $lines = explode("\n", $string);
        return implode('</w:t><w:br/><w:t>', $lines);
    }
}
