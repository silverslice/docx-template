Easy tool for replacing variables in docx documents
============================================================

## Install

`composer require silverslice/docx-template`

## Usage

Place variables in your docx-document as {foo}. Then replace them with `replace` method. Use `replaceMultiline` to replace variables with multiline strings.

```php

use Silverslice\DocxTemplate\Template;

require __DIR__ . '/vendor/autoload.php';

$template = new Template();

// open docx file
$template->open('test.docx')

    // replace one variable to another
    ->replace('one', 'two')

    // replace once more
    ->replace('foo', 'bar')
    
    // replace with multiline string 
    ->replaceMultiline('foo', 'one
        two
        three')

    // save docx document
    ->save('test.docx');
```