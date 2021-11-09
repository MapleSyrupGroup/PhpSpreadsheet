<?php

namespace PhpOffice\PhpSpreadsheetTests\Writer\Html;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheetTests\Functional;

class XssVulnerabilityTest extends Functional\AbstractFunctional
{
    public function providerXssRichText()
    {
        return [
            'script tag' => ['<script>alert(1)</script>'],
            'javascript tag' => ['javascript:alert(1)'],
            'with unicode' => ['java\u0003script:alert(1)'],
        ];
    }

    /**
     * @dataProvider providerXssRichText
     *
     * @param string $xssTextString
     */
    public function testXssInComment($xssTextString)
    {
        $spreadsheet = new Spreadsheet();

        $richText = new RichText();
        $richText->createText($xssTextString);

        $spreadsheet->getActiveSheet()->getCell('A1')->setValue('XSS Test');

        $spreadsheet->getActiveSheet()
            ->getComment('A1')
            ->setText($richText);

        $filename = tempnam(File::sysGetTempDir(), 'phpspreadsheet-test');

        $writer = IOFactory::createWriter($spreadsheet, 'Html');
        $writer->save($filename);

        $verify = file_get_contents($filename);
        // Ensure that executable js has been stripped from the comments
        self::assertNotContains($xssTextString, $verify);
    }
}
