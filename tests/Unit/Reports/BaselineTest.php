<?php
declare(strict_types=1);

namespace DR\CodeSnifferBaseline\Tests\Unit\Reports;

use DR\CodeSnifferBaseline\Reports\Baseline;
use DR\CodeSnifferBaseline\Util\CodeSignature;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Baseline::class)]
class BaselineTest extends TestCase
{
    private File&MockObject $file;

    protected function setUp(): void
    {
        $this->file = $this->createMock(File::class);
    }

    public function testGenerateFileReportEmptyShouldReturnFalse(): void
    {
        $report = new Baseline();
        static::assertFalse($report->generateFileReport(['errors' => 0, 'warnings' => 0, 'filename' => 'foo', 'messages' => []], $this->file));
    }

    public function testGenerateFileReportShouldPrintReport(): void
    {
        $reportData = [
            'filename' => '/test\\foobar.txt',
            'errors'   => 1,
            'warnings' => 0,
            'messages' => [5 => [[['source' => 'MySniff']]]],
        ];

        $tokens    = [
            [
                'content' => 'foobar',
                'line'    => 5,
            ],
        ];
        $signature = CodeSignature::createSignature($tokens, 5);
        $this->file->method('getTokens')->willReturn($tokens);

        $report = new Baseline();
        ob_start();
        static::assertTrue($report->generateFileReport($reportData, $this->file));
        $result = ob_get_clean();
        static::assertSame('<violation file="/test/foobar.txt" sniff="MySniff" signature="' . $signature . '"/>' . PHP_EOL, $result);
    }

    public function testGenerate(): void
    {
        $expected = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . PHP_EOL;
        $expected .= "<phpcs-baseline version=\"" . Config::VERSION . "\">";
        $expected .= "<violation file=\"/test/foobar.txt\" sniff=\"MySniff\"/>" . PHP_EOL;
        $expected .= "</phpcs-baseline>" . PHP_EOL;

        $report = new Baseline();
        ob_start();
        $report->generate('<violation file="/test/foobar.txt" sniff="MySniff"/>', 1, 1, 0, 1);
        $result = ob_get_clean();
        static::assertSame($expected, $result);
    }
}
