Easy tool for replacing words in docx documents
============================================================

## Install

`composer require silverslice/docx-template`

## Example of usage

```php

use Silverslice\DocxTemplate\Template;

require __DIR__ . '/vendor/autoload.php';

$template = new Template();

// open docx file
$template->open('test.docx')

    // replace one string to another
    ->replace('{one}', 'two')

    // replace once more
    ->replace('foo', 'bar')

    // you can replace multiple values too
    ->replace(['big', 'dog'], ['small', 'cat'])

    // save docx document
    ->save('test.docx');
```