<?php

namespace Silverslice\DocxTemplate\Tests;

use PHPUnit\Framework\TestCase;
use Silverslice\DocxTemplate\Template;

class TemplateTest extends TestCase
{
    public function testOpenFail()
    {
        $this->expectException(\Exception::class);
        $template = new Template();

        $template->open('testFail.docx');
    }

    public function testSaveFail()
    {
        $this->expectException(\Exception::class);
        $template = new Template();

        $template
            ->open(__DIR__ . '/test.docx')
            ->save(__DIR__ . '/dir/test.docx');
    }

    public function testReplace()
    {
        if (!is_dir(__DIR__ . '/output')) {
            mkdir(__DIR__ . '/output');
        }
        $replacedFile = __DIR__ . '/output/test.docx';

        $template = new Template();

        $template
            ->open(__DIR__ . '/test.docx')
            ->replace('name', '#Composer#')
            ->replace('head', '#management & marketing#')
            ->replace('library', '#libraries#')
            ->save($replacedFile);

        $this->assertFileExists($replacedFile);

        $contents = $this->getDocxContents($replacedFile);
        $this->assertEquals(2, substr_count($contents, '#Composer#'));
        $this->assertEquals(1, substr_count($contents, '#management &amp; marketing#'));
        $this->assertEquals(2, substr_count($contents, '#libraries#'));
    }

    public function testReplaceMultiline()
    {
        if (!is_dir(__DIR__ . '/output')) {
            mkdir(__DIR__ . '/output');
        }
        $replacedFile = __DIR__ . '/output/test_multiline.docx';

        $template = new Template();

        $string = 'One line
Two < line
Three';

        $template
            ->open(__DIR__ . '/test.docx')
            ->replaceMultiline('lines', $string)
            ->save($replacedFile);

        $this->assertFileExists($replacedFile);

        $contents = $this->getDocxContents($replacedFile);
        $this->assertEquals(1, substr_count($contents, 'One line</w:t><w:br/>'));
        $this->assertEquals(1, substr_count($contents, 'Two &lt; line</w:t><w:br/>'));
    }

    public function testReplaceNull()
    {
        error_reporting(E_ALL);
        if (!is_dir(__DIR__ . '/output')) {
            mkdir(__DIR__ . '/output');
        }
        $replacedFile = __DIR__ . '/output/test_null.docx';

        $template = new Template();

        $val = null;
        $template
            ->open(__DIR__ . '/test.docx')
            ->replace('name', $val)
            ->save($replacedFile);

        $this->assertFileExists($replacedFile);
    }

    public function testReplaceMultilineNull()
    {
        error_reporting(E_ALL);
        if (!is_dir(__DIR__ . '/output')) {
            mkdir(__DIR__ . '/output');
        }
        $replacedFile = __DIR__ . '/output/test_multiline_null.docx';

        $template = new Template();

        $string = null;

        $template
            ->open(__DIR__ . '/test.docx')
            ->replaceMultiline('lines', $string)
            ->save($replacedFile);

        $this->assertFileExists($replacedFile);
    }

    protected function getDocxContents($file)
    {
        $zip = new \ZipArchive();
        $zip->open($file);
        $contents = $zip->getFromName('word/document.xml');
        $zip->close();

        return $contents;
    }
}